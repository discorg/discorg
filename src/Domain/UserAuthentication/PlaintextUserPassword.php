<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class PlaintextUserPassword
{
    private string $password;

    private function __construct(string $password)
    {
        // TODO: validation
        $this->password = $password;
    }

    public static function fromString(string $password) : self
    {
        return new self($password);
    }

    public function toString() : string
    {
        return $this->password;
    }
}
