<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\CannotRegisterUser;
use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\ReadModel\IsUserRegistered;
use App\Domain\UserAuthentication\Repository\UserRepository;

final class RegisterUser
{
    /** @var IsUserRegistered */
    private $isUserRegistered;

    /** @var UserRepository */
    private $userRepository;

    /** @var PasswordHashing */
    private $passwordHashing;

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
    public function __invoke(EmailAddress $username, PlaintextUserPassword $password) : void
    {
        $user = User::register($username, $password, $this->isUserRegistered, $this->passwordHashing);

        $this->userRepository->save($user);
    }
}
