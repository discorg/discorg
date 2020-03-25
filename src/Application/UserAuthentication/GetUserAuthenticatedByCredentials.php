<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;

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
    public function __invoke(string $username, string $password) : AuthenticatedUserIdentifier
    {
        try {
            $user = $this->userRepository->get(EmailAddress::fromStoredValue($username));
        } catch (UserNotFound $e) {
            throw UserCannotBeAuthenticated::usernameNotFound();
        }

        return $user->getAuthenticatedIdentifier(
            PlaintextUserPassword::fromStoredValue($password),
            $this->passwordHashing,
        );
    }
}
