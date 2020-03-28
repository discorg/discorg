<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserCredentials;
use App\Domain\UserAuthentication\Username;

final class GetUserAuthenticatedByCredentials
{
    private UserRepository $userRepository;

    private PasswordHashing $passwordHashing;

    public function __construct(UserRepository $userRepository, PasswordHashing $passwordHashing)
    {
        $this->userRepository = $userRepository;
        $this->passwordHashing = $passwordHashing;
    }

    /**
     * @throws UserCannotBeAuthenticated
     */
    public function __invoke(UserCredentials $credentials) : AuthenticatedUserId
    {
        try {
            $user = $this->userRepository->getByUsername(Username::fromString($credentials->username()));
        } catch (UserNotFound $e) {
            throw UserCannotBeAuthenticated::usernameNotFound();
        }

        return $user->authenticateByCredentials(
            PlaintextUserPassword::fromStoredValue($credentials->password()),
            $this->passwordHashing,
        );
    }
}
