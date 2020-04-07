<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication\ReadModel;

use App\Domain\UserAuthentication\AuthenticatedUserId;
use RuntimeException;
use function sprintf;

final class UserNotFound extends RuntimeException
{
    public static function byId(AuthenticatedUserId $id) : self
    {
        return new self(sprintf('User not found by id "%s".', $id->toString()));
    }
}
