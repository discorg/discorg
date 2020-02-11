<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;

final class IsUserAuthenticated
{
    private UserRepository $userRepository;

    private PasswordHashing $passwordHashing;

    public function __construct(UserRepository $userRepository, PasswordHashing $passwordHashing)
    {
        $this->userRepository = $userRepository;
        $this->passwordHashing = $passwordHashing;
    }

    public function __invoke(EmailAddress $emailAddress, PlaintextUserPassword $password) : bool
    {
        try {
            $user = $this->userRepository->get($emailAddress);
        } catch (UserNotFound $exception) {
            return false;
        }

        return $user->isAuthenticatedBy($password, $this->passwordHashing);
    }
}
