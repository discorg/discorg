<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\EmailAddress;
use RuntimeException;
use function sprintf;

class CannotRegisterUser extends RuntimeException
{
    public static function emailAddressAlreadyRegistered(EmailAddress $emailAddress) : self
    {
        return new self(sprintf('Email address "%s" has already been registered.', $emailAddress->toString()));
    }
}
