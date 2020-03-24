<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\UserSessionToken;
use RuntimeException;
use function sprintf;

final class SessionNotFound extends RuntimeException
{
    public static function byToken(UserSessionToken $token) : self
    {
        return new self(sprintf('User session not found by token "%s".', $token->toString()));
    }
}
