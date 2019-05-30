<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Api;

use App\Infrastructure\Spotify\Session\InitializedSpotifySessionManager;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyWebApiFactory
{
    /** @var InitializedSpotifySessionManager */
    private $spotifySessionManager;

    public function __construct(InitializedSpotifySessionManager $spotifySessionManager)
    {
        $this->spotifySessionManager = $spotifySessionManager;
    }

    public function create() : SpotifyWebAPI
    {
        $api = new SpotifyWebAPI();
        $api->setAccessToken($this->spotifySessionManager->getSession()->getAccessToken());
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);

        return $api;
    }
}
