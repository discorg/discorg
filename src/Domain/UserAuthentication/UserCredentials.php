<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class UserCredentials
{
    private string $username;
    private string $password;

    private function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public static function fromStrings(string $username, string $password) : self
    {
        return new self($username, $password);
    }

    public function username() : string
    {
        return $this->username;
    }

    public function password() : string
    {
        return $this->password;
    }
}
