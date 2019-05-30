<?php

declare(strict_types=1);

namespace App\Infrastructure\Application;

use App\Infrastructure\Spotify\Session\SpotifySessionManager;
use App\Infrastructure\User\UserSessionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;
use RuntimeException;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpApplication
{
    /** @var UserSessionManager */
    private $userSessionManager;

    /** @var SpotifySessionManager */
    private $spotifySessionManager;

    /** @var ActionResolver */
    private $actionResolver;

    public function __construct(
        UserSessionManager $userSessionManager,
        SpotifySessionManager $spotifySessionManager,
        ActionResolver $actionResolver
    ) {
        $this->userSessionManager = $userSessionManager;
        $this->spotifySessionManager = $spotifySessionManager;
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

        $relay = new Relay([
            function (ServerRequestInterface $request) : ResponseInterface {
                return $this->processRequest($request);
            },
        ]);

        $response = $relay->handle($request);

        (new SapiEmitter())->emit($response);
    }

    public function processRequest(ServerRequestInterface $request) : ResponseInterface
    {
        $psr17Factory = new Psr17Factory();

        $response = $psr17Factory->createResponse();

        /** @var ResponseInterface $response */
        [$response, $userSession] = $this->userSessionManager->initialize($request, $response);

        try {
            $response = $this->spotifySessionManager->initialize($request, $response, $userSession);
        } catch (RuntimeException $exception) {
            $response->withStatus(500);
            $responseBodyAsStream = (new Psr17Factory())->createStream($exception->getMessage());
            $response->withBody($responseBodyAsStream);
        }

        if ($response->getHeader('Refresh') !== []) {
            return $response;
        }

        $action = $this->actionResolver->resolve($request);

        $response = $action($request, $response);

        return $response;
    }
}
