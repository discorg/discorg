<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Repository;

use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\UserSessionToken;
use RuntimeException;
use function sprintf;

final class UserNotFound extends RuntimeException
{
    public static function byEmailAddress(EmailAddress $emailAddress) : self
    {
        return new self(sprintf('User not found by email address "%s".', $emailAddress->toString()));
    }

    public static function byToken(UserSessionToken $token) : self
    {
        return new self(sprintf('User not found by token "%s".', $token->toString()));
    }
}
