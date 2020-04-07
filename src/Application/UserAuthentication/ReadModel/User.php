<?php

declare(strict_types=1);

namespace App\Application\UserAuthentication\ReadModel;

final class User
{
    private string $emailAddress;

    public function __construct(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function emailAddress() : string
    {
        return $this->emailAddress;
    }
}
