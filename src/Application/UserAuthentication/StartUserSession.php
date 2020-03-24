<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\Clock;
use App\Domain\UserAuthentication\Aggregate\CannotStartUserSession;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;

final class StartUserSession
{
    private UserRepository $userRepository;
    private Clock $clock;

    public function __construct(UserRepository $userRepository, Clock $clock)
    {
        $this->userRepository = $userRepository;
        $this->clock = $clock;
    }

    /**
     * @throws CannotStartUserSession
     */
    public function __invoke(AuthenticatedUserIdentifier $identifier, UserSessionToken $token) : void
    {
        try {
            $user = $this->userRepository->get($identifier->emailAddress());
        } catch (UserNotFound $e) {
            throw CannotStartUserSession::incorrectUserCredentials();
        }

        // TODO: pass frozen time
        $user->startSession($token, $this->clock->getCurrentTime());

        $this->userRepository->save($user);
    }
}
