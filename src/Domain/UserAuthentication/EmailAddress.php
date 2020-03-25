<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

use Assert\Assertion;

final class EmailAddress
{
    private string $emailAddress;

    private function __construct(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public static function fromString(string $emailAddress) : self
    {
        Assertion::email($emailAddress);

        return new self($emailAddress);
    }

    public static function fromStoredValue(string $emailAddress) : self
    {
        return new self($emailAddress);
    }

    public function toString() : string
    {
        return $this->emailAddress;
    }
}
