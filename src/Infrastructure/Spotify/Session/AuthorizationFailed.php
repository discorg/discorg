<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Session;

use RuntimeException;

final class AuthorizationFailed extends RuntimeException
{
    public static function accessTokenNotGranted() : self
    {
        return new static('Access token not granted.');
    }
}
