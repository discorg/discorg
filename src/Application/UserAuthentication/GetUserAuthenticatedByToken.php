<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;

final class GetUserAuthenticatedByToken
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws UserCannotBeAuthenticated
     */
    public function __invoke(UserSessionToken $token, DateTimeImmutable $at) : AuthenticatedUserId
    {
        try {
            $user = $this->userRepository->getByValidSessionToken($token, $at);
        } catch (UserNotFound $e) {
            throw UserCannotBeAuthenticated::validTokenNotFound();
        }

        return $user->authenticateByToken($token, $at);
    }
}
