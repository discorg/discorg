<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class Username
{
    private string $username;

    private function __construct(string $username)
    {
        $this->username = $username;
    }

    public static function fromString(string $username) : self
    {
        return new self($username);
    }

    public static function fromEmailAddress(EmailAddress $emailAddress) : self
    {
        return new self($emailAddress->toString());
    }

    public function toString() : string
    {
        return $this->username;
    }
}
