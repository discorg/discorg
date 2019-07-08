<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Repository;

use App\Domain\UserAuthentication\EmailAddress;
use RuntimeException;
use function sprintf;

final class UserNotFound extends RuntimeException
{
    public static function byEmailAddress(EmailAddress $emailAddress) : self
    {
        return new self(sprintf('User not found by email address "%s".', $emailAddress->toString()));
    }
}
