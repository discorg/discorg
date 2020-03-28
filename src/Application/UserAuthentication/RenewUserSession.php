<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;

final class RenewUserSession
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws UserNotFound
     * @throws SessionNotFound
     * @throws CannotModifySession
     */
    public function __invoke(AuthenticatedUserId $id, UserSessionToken $token, DateTimeImmutable $at) : void
    {
        $user = $this->userRepository->get($id);
        $user->renewSession($token, $at);

        $this->userRepository->save($user);
    }
}
