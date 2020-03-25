<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

use Assert\Assertion;

final class PlaintextUserPassword
{
    private string $password;

    private function __construct(string $password)
    {
        $this->password = $password;
    }

    public static function fromString(string $password) : self
    {
        Assertion::minLength($password, 7);

        return new self($password);
    }

    public static function fromStoredValue(string $password) : self
    {
        return new self($password);
    }

    public function toString() : string
    {
        return $this->password;
    }
}
