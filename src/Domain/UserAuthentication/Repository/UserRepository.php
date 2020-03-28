<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Repository;

use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Username;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;

interface UserRepository
{
    public function save(User $user) : void;

    /**
     * @throws UserNotFound
     */
    public function get(AuthenticatedUserId $id) : User;

    /**
     * @throws UserNotFound
     */
    public function getByUsername(Username $username) : User;

    /**
     * @throws UserNotFound
     */
    public function getByValidSessionToken(UserSessionToken $token, DateTimeImmutable $at) : User;
}
