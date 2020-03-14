<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class UserCredentials
{
    private EmailAddress $emailAddress;
    private PlaintextUserPassword $password;

    private function __construct(EmailAddress $emailAddress, PlaintextUserPassword $password)
    {
        $this->emailAddress = $emailAddress;
        $this->password = $password;
    }

    public static function fromStrings(string $emailAddress, string $password) : self
    {
        return new self(
            EmailAddress::fromString($emailAddress),
            PlaintextUserPassword::fromString($password),
        );
    }

    public function emailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }

    public function password() : PlaintextUserPassword
    {
        return $this->password;
    }
}
