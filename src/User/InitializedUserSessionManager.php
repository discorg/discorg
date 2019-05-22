<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\User;

interface InitializedUserSessionManager
{
    public function getSession() : UserSession;

    public function saveSession() : void;
}
