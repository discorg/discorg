<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class UserCredentials
{
    private EmailAddress $emailAddress;
    private PlaintextUserPassword $password;

    private function __construct()
    {
    }

    public static function fromEmailAddressAndPassword(
        EmailAddress $emailAddress,
        PlaintextUserPassword $password
    ) : self {
        $instance = new self();
        $instance->emailAddress = $emailAddress;
        $instance->password = $password;

        return $instance;
    }

    public static function fromStrings(string $emailAddress, string $password) : self
    {
        return self::fromEmailAddressAndPassword(
            // TODO: use non-validating constructors
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
