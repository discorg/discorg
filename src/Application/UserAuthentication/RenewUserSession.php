<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\Clock;
use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;

final class RenewUserSession
{
    private UserRepository $userRepository;
    private Clock $clock;

    public function __construct(UserRepository $userRepository, Clock $clock)
    {
        $this->userRepository = $userRepository;
        $this->clock = $clock;
    }

    /**
     * @throws UserNotFound
     * @throws SessionNotFound
     * @throws CannotModifySession
     */
    public function __invoke(UserSessionToken $token) : void
    {
        // TODO: pass frozen time
        $user = $this->userRepository->getByValidSessionToken($token, $this->clock->getCurrentTime());
        // TODO: pass frozen time
        $user->renewSession($token, $this->clock->getCurrentTime());

        $this->userRepository->save($user);
    }
}
