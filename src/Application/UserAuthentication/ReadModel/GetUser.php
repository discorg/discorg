<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication\ReadModel;

use App\Domain\UserAuthentication\AuthenticatedUserId;

final class GetUser
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws UserNotFound
     */
    public function __invoke(AuthenticatedUserId $id) : User
    {
        return $this->repository->get($id);
    }
}
