<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Application;

use Bouda\SpotifyAlbumTagger\Spotify\Session\InitializableSpotifySessionManager;
use Bouda\SpotifyAlbumTagger\User\InitializableUserSessionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class HttpApplication
{
    /** @var InitializableUserSessionManager */
    private $userSessionManager;

    /** @var InitializableSpotifySessionManager */
    private $spotifySessionManager;

    /** @var ActionResolver */
    private $actionResolver;

    public function __construct(
        InitializableUserSessionManager $userSessionManager,
        InitializableSpotifySessionManager $spotifySessionManager,
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

        $response = $this->processRequest($request);

        (new SapiEmitter())->emit($response);
    }

    public function processRequest(ServerRequestInterface $request) : ResponseInterface
    {
        $psr17Factory = new Psr17Factory();

        $response = $psr17Factory->createResponse();

        $response = $this->userSessionManager->initialize($request, $response);

        $response = $this->spotifySessionManager->initialize($request, $response);

        if ($response->getHeader('Refresh') !== []) {
            return $response;
        }

        $action = $this->actionResolver->resolve($request);

        $response = $action($request, $response);

        return $response;
    }
}
