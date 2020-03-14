<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class UserSessionToken
{
    private string $token;

    private function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function generate() : self
    {
        return new self('12345');
    }

    public static function fromStoredValue(string $token) : self
    {
        return new self($token);
    }

    public function toString() : string
    {
        return $this->token;
    }
}
