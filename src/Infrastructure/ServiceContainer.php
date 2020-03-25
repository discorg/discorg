<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Application\UserAuthentication\EndUserSession;
use App\Application\UserAuthentication\GetUserAuthenticatedByCredentials;
use App\Application\UserAuthentication\RegisterUser;
use App\Application\UserAuthentication\RenewUserSession;
use App\Application\UserAuthentication\StartUserSession;
use App\Domain\UserAuthentication\Aggregate\IsUserRegistered;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Infrastructure\Http\Actions\Albums\GetAlbums;
use App\Infrastructure\Http\Actions\Api\CreateUser;
use App\Infrastructure\Http\Actions\Api\CreateUserSession;
use App\Infrastructure\Http\Actions\Api\DeleteUserSession;
use App\Infrastructure\Http\Actions\Api\GetHealthCheck;
use App\Infrastructure\Http\Actions\Api\GetSessionCollection;
use App\Infrastructure\Http\Actions\Get;
use App\Infrastructure\Http\Api\ApiOperationFindingMiddleware;
use App\Infrastructure\Http\Api\ApiRequestAndResponseValidatingMiddleware;
use App\Infrastructure\Http\Authentication\BasicUserAuthenticationMiddleware;
use App\Infrastructure\Http\Authentication\TokenUserAuthenticationMiddleware;
use App\Infrastructure\Http\HandlerFactoryCollection;
use App\Infrastructure\Http\HttpApplication;
use App\Infrastructure\Http\MiddlewareStack;
use App\Infrastructure\Http\MiddlewareStackByUriPath;
use App\Infrastructure\Http\RequestHandlingMiddleware;
use App\Infrastructure\Http\SpotifySessionMiddleware;
use App\Infrastructure\Http\UserSessionMiddleware;
use App\Infrastructure\Spotify\Session\SpotifySessionFactory;
use App\Infrastructure\Spotify\SpotifyUserLibraryFacade;
use App\Infrastructure\User\UserSessionManager;
use App\Infrastructure\UserAuthentication\InMemoryUserRepository;
use App\Infrastructure\UserAuthentication\IsUserRegisteredUsingRepository;
use App\Infrastructure\UserAuthentication\PhpPasswordHashing;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\SchemaFactory\YamlFactory;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Server\RequestHandlerInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use Throwable;
use function array_key_exists;
use function file_get_contents;
use function getenv;
use function sprintf;

final class ServiceContainer
{
    /** @var mixed[] */
    private array $reusableServicesByType = [];

    public function httpApplication() : HttpApplication
    {
        return new HttpApplication(
            MiddlewareStackByUriPath::from(
                '/api/',
                MiddlewareStack::fromArray(
                    $this->apiOperationFindingMiddleware(),
                    $this->basicUserAuthenticationMiddleware(),
                    $this->tokenUserAuthenticationMiddleware(),
                    $this->apiRequestAndResponseValidatingMiddleware(),
                    $this->apiRequestHandlingMiddleware(),
                ),
            ),
            MiddlewareStackByUriPath::from(
                '/',
                MiddlewareStack::fromArray(
                    $this->userSessionMiddleware(),
                    $this->spotifySessionMiddleware(),
                    $this->webRequestHandlingMiddleware(),
                ),
            ),
        );
    }

    private function apiOperationFindingMiddleware() : ApiOperationFindingMiddleware
    {
        return new ApiOperationFindingMiddleware($this->apiSchema(), $this->psr17factory());
    }

    private function basicUserAuthenticationMiddleware() : BasicUserAuthenticationMiddleware
    {
        return new BasicUserAuthenticationMiddleware(
            $this->specFinder(),
            $this->psr17factory(),
            $this->getUserAuthenticatedByCredentials(),
        );
    }

    private function tokenUserAuthenticationMiddleware() : TokenUserAuthenticationMiddleware
    {
        return new TokenUserAuthenticationMiddleware(
            $this->specFinder(),
            $this->psr17factory(),
            $this->renewUserSession(),
        );
    }

    private function apiRequestAndResponseValidatingMiddleware() : ApiRequestAndResponseValidatingMiddleware
    {
        return new ApiRequestAndResponseValidatingMiddleware(
            $this->serverRequestValidator(),
            $this->responseValidator(),
            $this->psr17factory(),
        );
    }

    private function serverRequestValidator() : ServerRequestValidator
    {
        $schema = $this->apiSchema();

        static $validator;
        try {
            return $validator
                ?? $validator = (new ValidatorBuilder())->fromSchema($schema)->getServerRequestValidator();
        } catch (Throwable $e) {
            throw new LogicException('Json server request validator initialization failed.', 0, $e);
        }
    }

    private function responseValidator() : ResponseValidator
    {
        $schema = $this->apiSchema();

        static $validator;
        try {
            return $validator
                ?? $validator = (new ValidatorBuilder())->fromSchema($schema)->getResponseValidator();
        } catch (Throwable $e) {
            throw new LogicException('Json response validator initialization failed.', 0, $e);
        }
    }

    private function apiSchema() : OpenApi
    {
        if (array_key_exists(Schema::class, $this->reusableServicesByType)) {
            return $this->reusableServicesByType[Schema::class];
        }

        $specificationFilename = __DIR__ . '/Http/Actions/Api/openapi.yaml';
        $specification = file_get_contents($specificationFilename);

        if ($specification === false) {
            throw new LogicException(
                sprintf(
                    'Api specification not found at "%s".',
                    $specificationFilename,
                )
            );
        }

        $schema = (new YamlFactory($specification))->createSchema();
        $this->reusableServicesByType[Schema::class] = $schema;

        return $schema;
    }

    private function specFinder() : SpecFinder
    {
        return new SpecFinder($this->apiSchema());
    }

    private function apiRequestHandlingMiddleware() : RequestHandlingMiddleware
    {
        return new RequestHandlingMiddleware(HandlerFactoryCollection::fromArray([
            'GET /api/v1/health-check' => function () : RequestHandlerInterface {
                return new GetHealthCheck(
                    $this->psr17factory(),
                );
            },
            'POST /api/v1/user' => function () : RequestHandlerInterface {
                return new CreateUser(
                    $this->registerUser(),
                    $this->psr17factory(),
                );
            },
            'POST /api/v1/user/me/session' => function () : RequestHandlerInterface {
                return new CreateUserSession(
                    $this->startUserSession(),
                    $this->psr17factory(),
                    $this->psr17factory(),
                );
            },
            'GET /api/v1/user/me/session' => function () : RequestHandlerInterface {
                return new GetSessionCollection(
                    $this->psr17factory(),
                    $this->psr17factory(),
                );
            },
            'DELETE /api/v1/user/me/session' => function () : RequestHandlerInterface {
                return new DeleteUserSession(
                    $this->endUserSession(),
                    $this->psr17factory(),
                );
            },
        ]));
    }

    private function userSessionMiddleware() : UserSessionMiddleware
    {
        return new UserSessionMiddleware($this->userSessionManager());
    }

    private function spotifySessionMiddleware() : SpotifySessionMiddleware
    {
        return new SpotifySessionMiddleware($this->spotifySessionFactory());
    }

    private function webRequestHandlingMiddleware() : RequestHandlingMiddleware
    {
        return new RequestHandlingMiddleware(HandlerFactoryCollection::fromArray([
            'GET /' => function () : RequestHandlerInterface {
                return new Get(
                    $this->psr17factory(),
                );
            },
            'GET /albums' => function () : RequestHandlerInterface {
                return new GetAlbums(
                    $this->spotifyUserLibrary(),
                    $this->psr17factory(),
                );
            },
        ]));
    }

    private function userSessionManager() : UserSessionManager
    {
        return new UserSessionManager();
    }

    private function spotifySessionFactory() : SpotifySessionFactory
    {
        return new SpotifySessionFactory(
            (string) getenv('SPOTIFY_CLIENT_ID'),
            (string) getenv('SPOTIFY_CLIENT_SECRET'),
            [
                // Library
                'user-library-read',
                'user-library-modify',
                // Playlists
                'playlist-read-private',
                'playlist-modify-public',
                'playlist-modify-private',
                'playlist-read-collaborative',
                // Listening History
                'user-read-recently-played',
                'user-top-read',
                // Users
                'user-read-private',
                'user-read-email',
                'user-read-birthdate',
                // Spotify Connect
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-state',
                // Follow
                'user-follow-modify',
                'user-follow-read',
            ]
        );
    }

    private function psr17factory() : Psr17Factory
    {
        static $factory;

        return $factory ?? $factory = new Psr17Factory();
    }

    private function spotifyUserLibrary() : SpotifyUserLibraryFacade
    {
        return new SpotifyUserLibraryFacade($this->spotifyWebAPI());
    }

    private function spotifyWebAPI() : SpotifyWebAPI
    {
        $api = new SpotifyWebAPI();
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);

        return $api;
    }

    private function registerUser() : RegisterUser
    {
        return new RegisterUser(
            $this->isUserRegistered(),
            $this->userRepository(),
            $this->passwordHashing()
        );
    }

    private function isUserRegistered() : IsUserRegistered
    {
        return new IsUserRegisteredUsingRepository($this->userRepository());
    }

    private function userRepository() : UserRepository
    {
        return $this->reusableServicesByType[InMemoryUserRepository::class]
            ?? $this->reusableServicesByType[InMemoryUserRepository::class] = new InMemoryUserRepository();
    }

    private function passwordHashing() : PasswordHashing
    {
        return new PhpPasswordHashing();
    }

    private function startUserSession() : StartUserSession
    {
        return new StartUserSession(
            $this->userRepository(),
            $this->clock(),
        );
    }

    private function endUserSession() : EndUserSession
    {
        return new EndUserSession(
            $this->userRepository(),
            $this->clock(),
        );
    }

    public function clock() : FreezableClock
    {
        return $this->reusableServicesByType[FreezableClock::class]
            ?? $this->reusableServicesByType[FreezableClock::class] = new FreezableClock(new PhpClock());
    }

    private function getUserAuthenticatedByCredentials() : GetUserAuthenticatedByCredentials
    {
        return new GetUserAuthenticatedByCredentials($this->userRepository(), $this->passwordHashing());
    }

    private function renewUserSession() : RenewUserSession
    {
        return new RenewUserSession($this->userRepository(), $this->clock());
    }
}
