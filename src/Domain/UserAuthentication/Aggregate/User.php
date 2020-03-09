<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\ReadModel\IsUserRegistered;
use App\Domain\UserAuthentication\UserPasswordHash;

final class User
{
    private EmailAddress $emailAddress;

    private UserPasswordHash $passwordHash;

    private function __construct(EmailAddress $emailAddress, UserPasswordHash $passwordHash)
    {
        $this->emailAddress = $emailAddress;
        $this->passwordHash = $passwordHash;
    }

    /**
     * @throws CannotRegisterUser
     */
    public static function register(
        EmailAddress $emailAddress,
        PlaintextUserPassword $password,
        IsUserRegistered $isUserRegistered,
        PasswordHashing $hashing
    ) : self {
        if ($isUserRegistered($emailAddress)) {
            throw CannotRegisterUser::emailAddressAlreadyRegistered($emailAddress);
        }

        $passwordHash = UserPasswordHash::fromPassword($password, $hashing);

        return new self($emailAddress, $passwordHash);
    }

    public static function fromStoredValues(EmailAddress $emailAddress, UserPasswordHash $passwordHash) : self
    {
        return new self($emailAddress, $passwordHash);
    }

    public function isAuthenticatedBy(PlaintextUserPassword $password, PasswordHashing $passwordHashing) : bool
    {
        return $this->passwordHash->matches($password, $passwordHashing);
    }

    public function getEmailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }
}
