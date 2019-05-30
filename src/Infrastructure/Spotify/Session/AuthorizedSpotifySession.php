<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Session;

interface AuthorizedSpotifySession
{
    public function getAccessToken() : string;

    public function getRefreshToken() : string;

    public function refresh() : AuthorizedSpotifySession;
}
