<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

use Bouda\SpotifyAlbumTagger\User\InitializedUserSessionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;

class SpotifySessionManager implements InitializableSpotifySessionManager, InitializedSpotifySessionManager
{
    /** @var InitializedUserSessionManager */
    private $userSessionManager;

    /** @var SpotifySessionFactory */
    private $spotifySessionFactory;

    /** @var AuthorizedSpotifySession */
    private $spotifySession;

    public function __construct(
        InitializedUserSessionManager $userSessionManager,
        SpotifySessionFactory $spotifySessionFactory
    ) {
        $this->userSessionManager = $userSessionManager;
        $this->spotifySessionFactory = $spotifySessionFactory;
    }

    public function initialize(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $userSession = $this->userSessionManager->getSession();

        $requestQueryParameters = $request->getQueryParams();
        if (isset($requestQueryParameters['code'])) {
            $spotifySession = $this->spotifySessionFactory->createAuthorizable($this->getRedirectUri($request));

            $code = $requestQueryParameters['code'];
            $spotifySession = $spotifySession->authorize($code);

            $userSession->setupSpotify($spotifySession->getAccessToken(), $spotifySession->getRefreshToken());
            $response = $this->userSessionManager->saveSession($response);

            $response = $response->withBody(
                (new Psr17Factory())->createStream('Authorizing spotify session with code.')
            );
            $response = $response->withHeader('Refresh', '1;index.php');

            return $response;
        }

        if (! $userSession->isInitialized()) {
            $spotifySession = $this->spotifySessionFactory->createAuthorizable($this->getRedirectUri($request));

            $url = $spotifySession->getAuthorizeUrl();

            $response = $response->withBody(
                (new Psr17Factory())->createStream('Redirecting to spotify.')
            );
            $response = $response->withHeader('Refresh', '1;' . $url);

            return $response;
        }

        $spotifySession = $this->spotifySessionFactory->createAuthorized(
            $this->getRedirectUri($request),
            $userSession->getSpotifyAccessToken(),
            $userSession->getSpotifyRefreshToken(),
        );

        $this->spotifySession = $spotifySession;

        return $this->refreshTokenIfNeeded($response);
    }

    public function getSession() : AuthorizedSpotifySession
    {
        return $this->spotifySession;
    }

    private function refreshTokenIfNeeded(ResponseInterface $response) : ResponseInterface
    {
        $spotifySession = $this->spotifySession;

        try {
            $api = new SpotifyWebAPI();
            $api->setAccessToken($spotifySession->getAccessToken());
            $api->me();
        } catch (SpotifyWebAPIException $e) {
            if ($e->getCode() === 401) {
                $spotifySession->refresh();

                $userSession = $this->userSessionManager->getSession();
                $userSession->setupSpotify($spotifySession->getAccessToken(), $spotifySession->getRefreshToken());

                $response = $this->userSessionManager->saveSession($response);
            }
        }

        return $response;
    }

    private function getRedirectUri(ServerRequestInterface $request) : string
    {
        $uri = $request->getUri()->withPath('')->withQuery('')->withFragment('');

        return $uri->__toString();
    }
}
