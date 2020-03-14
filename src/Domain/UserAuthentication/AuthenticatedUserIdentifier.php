<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class AuthenticatedUserIdentifier
{
    private EmailAddress $emailAddress;

    private function __construct()
    {
    }

    public static function fromEmailAddress(EmailAddress $emailAddress) : self
    {
        $instance = new self();
        $instance->emailAddress = $emailAddress;

        return $instance;
    }

    public function emailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }
}
