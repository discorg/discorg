<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use RuntimeException;

final class CannotStartUserSession extends RuntimeException
{
    public static function incorrectUserCredentials() : self
    {
        return new self('Cannot start session, incorrect credentials.');
    }
}
