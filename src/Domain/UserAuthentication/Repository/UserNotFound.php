<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Repository;

use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\Username;
use App\Domain\UserAuthentication\UserSessionToken;
use RuntimeException;
use function sprintf;

final class UserNotFound extends RuntimeException
{
    public static function byId(AuthenticatedUserIdentifier $id) : self
    {
        return new self(sprintf('User not found by id "%s".', $id->toString()));
    }

    public static function byUsername(Username $username) : self
    {
        return new self(sprintf('User not found by username "%s".', $username->toString()));
    }

    public static function byToken(UserSessionToken $token) : self
    {
        return new self(sprintf('User not found by token "%s".', $token->toString()));
    }
}
