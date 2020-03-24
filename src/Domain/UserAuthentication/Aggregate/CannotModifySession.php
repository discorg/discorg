<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use RuntimeException;

final class CannotModifySession extends RuntimeException
{
    public static function alreadyExpired() : self
    {
        return new self('Session already expired.');
    }
}
