<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\User;

use function array_key_exists;
use function serialize;
use function setcookie;
use function time;
use function unserialize;

class UserSessionManager implements InitializableUserSessionManager, InitializedUserSessionManager
{
    /** @var UserSession */
    private $session;

    public function initialize() : InitializedUserSessionManager
    {
        if (array_key_exists('userSession', $_COOKIE)) {
            $this->session = unserialize($_COOKIE['userSession']);
        } else {
            $this->session = new UserSession();
            $this->saveSession();
        }

        return $this;
    }

    public function getSession() : UserSession
    {
        return $this->session;
    }

    public function saveSession() : void
    {
        setcookie('userSession', serialize($this->session), time() + 30 * 24 * 3600);
    }
}
