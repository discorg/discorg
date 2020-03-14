<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class EmailAddress
{
    private string $emailAddress;

    private function __construct(string $emailAddress)
    {
        // TODO: validation
        $this->emailAddress = $emailAddress;
    }

    public static function fromString(string $emailAddress) : self
    {
        return new self($emailAddress);
    }

    public function toString() : string
    {
        return $this->emailAddress;
    }
}
