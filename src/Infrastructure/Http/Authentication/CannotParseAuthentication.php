<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use RuntimeException;

final class CannotParseAuthentication extends RuntimeException
{
    public static function headerNotFound() : self
    {
        return new self('Cannot parse authentication, header not found.');
    }

    public static function invalidEncoding() : self
    {
        return new self('Cannot parse authentication, invalid encoding.');
    }

    public static function invalidCredentialsString() : self
    {
        return new self('Cannot parse authentication, invalid credentials string.');
    }
}
