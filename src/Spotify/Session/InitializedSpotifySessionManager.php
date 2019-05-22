<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

interface InitializedSpotifySessionManager
{
    public function getSession() : AuthorizedSpotifySession;
}
