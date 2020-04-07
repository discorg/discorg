<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication\ReadModel;

use App\Domain\UserAuthentication\AuthenticatedUserId;

interface UserRepository
{
    /**
     * @throws UserNotFound
     */
    public function get(AuthenticatedUserId $id) : User;
}
