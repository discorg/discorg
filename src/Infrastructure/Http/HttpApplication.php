<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Application\ActionResolver;
use App\Infrastructure\Spotify\Session\SpotifySessionFactory;
use App\Infrastructure\User\UserSessionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpApplication
{
    /** @var UserSessionManager */
    private $userSessionManager;

    /** @var SpotifySessionFactory */
    private $spotifySessionFactory;

    /** @var ActionResolver */
    private $actionResolver;

    public function __construct(
        UserSessionManager $userSessionManager,
        SpotifySessionFactory $spotifySessionFactory,
        ActionResolver $actionResolver
    ) {
        $this->userSessionManager = $userSessionManager;
        $this->spotifySessionFactory = $spotifySessionFactory;
        $this->actionResolver = $actionResolver;
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
            function (ServerRequestInterface $request) : ResponseInterface {
                return $this->processRequest($request);
            },
        ]);

        return $relay->handle($request);
    }

    private function processRequest(ServerRequestInterface $request) : ResponseInterface
    {
        $psr17Factory = new Psr17Factory();

        $response = $psr17Factory->createResponse();

        $action = $this->actionResolver->resolve($request);

        $response = $action($request, $response);

        return $response;
    }
}
