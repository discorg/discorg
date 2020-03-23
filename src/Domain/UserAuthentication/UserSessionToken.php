<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

use Ramsey\Uuid\Uuid;

final class UserSessionToken
{
    private string $token;

    private function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function generate() : self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromStoredValue(string $token) : self
    {
        return new self($token);
    }

    public function toString() : string
    {
        return $this->token;
    }

    public function equals(UserSessionToken $token) : bool
    {
        return $this->token === $token->token;
    }
}
