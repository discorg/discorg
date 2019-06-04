<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Http\Actions\Albums\GetAlbums;
use App\Infrastructure\Http\Actions\Get;
use App\Infrastructure\ServiceContainer;
use App\Infrastructure\Spotify\Session\SpotifySessionFactory;
use App\Infrastructure\User\UserSessionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Relay\Relay;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpApplication
{
    /** @var UserSessionManager */
    private $userSessionManager;

    /** @var SpotifySessionFactory */
    private $spotifySessionFactory;

    /** @var ServiceContainer */
    private $container;

    public function __construct(
        UserSessionManager $userSessionManager,
        SpotifySessionFactory $spotifySessionFactory,
        ServiceContainer $container
    ) {
        $this->userSessionManager = $userSessionManager;
        $this->spotifySessionFactory = $spotifySessionFactory;
        $this->container = $container;
    }

    public function run() : void
    {
        $psr17Factory = new Psr17Factory();

        $creator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        $request = $creator->fromGlobals();

        $response = $this->processRequestThroughMiddlewareStack($request);

        (new SapiEmitter())->emit($response);
    }

    public function processRequestThroughMiddlewareStack(ServerRequestInterface $request) : ResponseInterface
    {
        $relay = new Relay([
            new UserSessionMiddleware($this->userSessionManager),
            new SpotifySessionMiddleware($this->spotifySessionFactory),
            new RequestHandlingMiddleware(HandlerFactoryCollection::fromArray([
                'GET /' => function () : RequestHandlerInterface {
                    return $this->container->getHttpHandler(Get::class);
                },
                'GET /albums' => function () : RequestHandlerInterface {
                    return $this->container->getHttpHandler(GetAlbums::class);
                },
            ])),
        ]);

        return $relay->handle($request);
    }
}
