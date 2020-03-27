<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use RuntimeException;

final class UserCannotBeAuthenticated extends RuntimeException
{
    public static function passwordDoesNotMatch() : self
    {
        return new self('User cannot be authenticated');
    }

    public static function usernameNotFound() : self
    {
        return new self('User cannot be authenticated');
    }

    public static function validTokenNotFound() : self
    {
        return new self('User cannot be authenticated');
    }
}
