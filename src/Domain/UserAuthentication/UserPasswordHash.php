<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class UserPasswordHash
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromString(string $hash) : self
    {
        return new self($hash);
    }

    public static function fromPassword(PlaintextUserPassword $password, PasswordHashing $hashing) : self
    {
        return new self($hashing->hash($password->toString()));
    }

    public function matches(PlaintextUserPassword $password, PasswordHashing $hashing) : bool
    {
        return $hashing->verify($password->toString(), $this->hash);
    }
}
