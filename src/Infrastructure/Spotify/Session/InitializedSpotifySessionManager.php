<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Session;

interface InitializedSpotifySessionManager
{
    public function getSession() : AuthorizedSpotifySession;
}
