<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Repository;

use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\EmailAddress;

interface UserRepository
{
    public function save(User $user) : void;

    /**
     * @throws UserNotFound
     */
    public function get(EmailAddress $emailAddress) : User;
}
