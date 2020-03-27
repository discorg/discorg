<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\Clock;
use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;

final class EndUserSession
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
    public function __invoke(AuthenticatedUserIdentifier $identifier, UserSessionToken $token) : void
    {
        $user = $this->userRepository->get($identifier);
        // TODO: pass frozen time
        $user->endSession($token, $this->clock->getCurrentTime());

        $this->userRepository->save($user);
    }
}
