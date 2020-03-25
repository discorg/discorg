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
use App\Domain\UserAuthentication\UserCredentials;

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
    public function __invoke(UserCredentials $credentials) : AuthenticatedUserIdentifier
    {
        try {
            // TODO: get by username
            $user = $this->userRepository->get(EmailAddress::fromStoredValue($credentials->username()));
        } catch (UserNotFound $e) {
            throw UserCannotBeAuthenticated::usernameNotFound();
        }

        return $user->getAuthenticatedIdentifier(
            PlaintextUserPassword::fromStoredValue($credentials->password()),
            $this->passwordHashing,
        );
    }
}
