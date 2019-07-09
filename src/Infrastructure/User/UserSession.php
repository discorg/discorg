<?php

declare(strict_types=1);

namespace App\Infrastructure\User;

use LogicException;

final class UserSession
{
    /** @var string|null */
    private $spotifyAccessToken;

    /** @var string|null */
    private $spotifyRefreshToken;

    public function setupSpotify(string $accessToken, string $refreshToken) : void
    {
        $this->spotifyAccessToken = $accessToken;
        $this->spotifyRefreshToken = $refreshToken;
    }

    public function getSpotifyAccessToken() : string
    {
        if ($this->spotifyAccessToken === null) {
            throw new LogicException('Uninitialized.');
        }

        return $this->spotifyAccessToken;
    }

    public function getSpotifyRefreshToken() : string
    {
        if ($this->spotifyRefreshToken === null) {
            throw new LogicException('Uninitialized.');
        }

        return $this->spotifyRefreshToken;
    }

    public function isInitialized() : bool
    {
        return $this->spotifyAccessToken !== null
            && $this->spotifyRefreshToken !== null;
    }
}
