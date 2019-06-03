<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Spotify\Session\AuthorizableSpotifySession;
use App\Infrastructure\Spotify\Session\AuthorizedSpotifySession;
use App\Infrastructure\Spotify\Session\SpotifySessionFactory;
use App\Infrastructure\User\UserSession;
use Assert\Assertion;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;

final class SpotifySessionMiddleware implements MiddlewareInterface
{
    /** @var SpotifySessionFactory */
    private $spotifySessionFactory;

    public function __construct(SpotifySessionFactory $spotifySessionFactory)
    {
        $this->spotifySessionFactory = $spotifySessionFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        /** @var UserSession $userSession */
        $userSession = $request->getAttribute(UserSession::class);
        Assertion::isInstanceOf($userSession, UserSession::class);

        try {
            if (! $userSession->isInitialized()) {
                $requestQueryParameters = $request->getQueryParams();
                if (isset($requestQueryParameters['code'])) {
                    $spotifySession = $this->spotifySessionFactory->createAuthorizable($this->getRedirectUri($request));

                    $code = $requestQueryParameters['code'];
                    $spotifySession = $spotifySession->authorize($code);

                    $userSession->setupSpotify($spotifySession->getAccessToken(), $spotifySession->getRefreshToken());

                    return $this->returnFromSpotifyResponse();
                }

                $spotifySession = $this->spotifySessionFactory->createAuthorizable($this->getRedirectUri($request));

                return $this->redirectToSpotifyResponse($spotifySession);
            }

            $spotifySession = $this->spotifySessionFactory->createAuthorized(
                $this->getRedirectUri($request),
                $userSession->getSpotifyAccessToken(),
                $userSession->getSpotifyRefreshToken(),
            );

            $spotifySession = $this->refreshTokenIfNeeded($spotifySession);
        } catch (RuntimeException $exception) {
            $response = (new Psr17Factory())->createResponse(500);
            $responseBodyAsStream = (new Psr17Factory())->createStream($exception->getMessage());
            $response->withBody($responseBodyAsStream);

            return $response;
        }

        Assertion::true($userSession->isInitialized());

        $userSession->setupSpotify($spotifySession->getAccessToken(), $spotifySession->getRefreshToken());

        $request = $request->withAttribute(UserSession::class, $userSession);
        $request = $request->withAttribute(AuthorizedSpotifySession::class, $spotifySession);

        return $handler->handle($request);
    }

    private function redirectToSpotifyResponse(AuthorizableSpotifySession $spotifySession) : ResponseInterface
    {
        return (new Psr17Factory())->createResponse()
            ->withBody(
                (new Psr17Factory())->createStream('Redirecting to spotify.')
            )
            ->withHeader('Refresh', '1;' . $spotifySession->getAuthorizeUrl());
    }

    private function returnFromSpotifyResponse() : ResponseInterface
    {
        return (new Psr17Factory())->createResponse()
            ->withBody(
                (new Psr17Factory())->createStream('Authorizing spotify session with code.')
            )
            ->withHeader('Refresh', '1;index.php');
    }

    private function refreshTokenIfNeeded(AuthorizedSpotifySession $spotifySession) : AuthorizedSpotifySession
    {
        try {
            $api = new SpotifyWebAPI();
            $api->setAccessToken($spotifySession->getAccessToken());
            $api->me();
        } catch (SpotifyWebAPIException $e) {
            if ($e->getCode() === 401) {
                $spotifySession->refresh();
            }
        }

        return $spotifySession;
    }

    private function getRedirectUri(ServerRequestInterface $request) : string
    {
        $uri = $request->getUri()->withPath('')->withQuery('')->withFragment('');

        return $uri->__toString();
    }
}
