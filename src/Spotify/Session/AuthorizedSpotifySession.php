<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

interface AuthorizedSpotifySession
{
    public function getAccessToken() : string;

    public function getRefreshToken() : string;

    public function refresh() : AuthorizedSpotifySession;
}
