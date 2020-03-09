<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAuthentication;

use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\ReadModel\IsUserRegistered;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;

final class IsUserRegisteredUsingRepository implements IsUserRegistered
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(EmailAddress $emailAddress) : bool
    {
        try {
            $this->userRepository->get($emailAddress);

            return true;
        } catch (UserNotFound $exception) {
            return false;
        }
    }
}
