<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\Clock;
use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;

final class GetUserAuthenticatedByToken
{
    private UserRepository $userRepository;
    private Clock $clock;

    public function __construct(UserRepository $userRepository, Clock $clock)
    {
        $this->userRepository = $userRepository;
        $this->clock = $clock;
    }

    /**
     * @throws UserCannotBeAuthenticated
     */
    public function __invoke(UserSessionToken $token) : AuthenticatedUserId
    {
        try {
            // TODO: pass frozen time
            $user = $this->userRepository->getByValidSessionToken($token, $this->clock->getCurrentTime());
        } catch (UserNotFound $e) {
            throw UserCannotBeAuthenticated::validTokenNotFound();
        }

        // TODO: pass frozen time
        return $user->authenticateByToken($token, $this->clock->getCurrentTime());
    }
}
