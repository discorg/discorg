<?php

declare(strict_types=1);

namespace App\Spotify\Session;

interface InitializedSpotifySessionManager
{
    public function getSession() : AuthorizedSpotifySession;
}
