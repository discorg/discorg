<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\CannotRegisterUser;
use App\Domain\UserAuthentication\Aggregate\IsUserRegistered;
use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\Repository\UserRepository;

final class RegisterUser
{
    private IsUserRegistered $isUserRegistered;

    private UserRepository $userRepository;

    private PasswordHashing $passwordHashing;

    public function __construct(
        IsUserRegistered $isUserRegistered,
        UserRepository $userRepository,
        PasswordHashing $passwordHashing
    ) {
        $this->isUserRegistered = $isUserRegistered;
        $this->userRepository = $userRepository;
        $this->passwordHashing = $passwordHashing;
    }

    /**
     * @throws CannotRegisterUser
     */
    public function __invoke(EmailAddress $emailAddress, PlaintextUserPassword $password) : void
    {
        $user = User::register($emailAddress, $password, $this->isUserRegistered, $this->passwordHashing);

        $this->userRepository->save($user);
    }
}
