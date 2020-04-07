<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAuthentication\ReadModel;

use App\Application\UserAuthentication\ReadModel\User;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;

final class UserRepositoryUsingUserRepository implements \App\Application\UserAuthentication\ReadModel\UserRepository
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function get(AuthenticatedUserId $id) : User
    {
        try {
            return new User($this->userRepository->get($id)->emailAddress()->toString());
        } catch (UserNotFound $e) {
            throw \App\Application\UserAuthentication\ReadModel\UserNotFound::byId($id);
        }
    }
}
