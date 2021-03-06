<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\CannotStartUserSession;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;

final class StartUserSession
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws CannotStartUserSession
     */
    public function __invoke(AuthenticatedUserId $id, UserSessionToken $tokenCandidate, DateTimeImmutable $at) : void
    {
        try {
            $user = $this->userRepository->get($id);
        } catch (UserNotFound $e) {
            throw CannotStartUserSession::incorrectUserCredentials();
        }

        $user->startSession($tokenCandidate, $at);

        $this->userRepository->save($user);
    }
}
