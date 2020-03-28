<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

final class AuthenticatedUserIdentifier
{
    private UserId $userId;

    private function __construct(UserId $userId)
    {
        $this->userId = $userId;
    }

    public static function fromId(UserId $userId) : self
    {
        return new self($userId);
    }

    public function toString() : string
    {
        return $this->userId->toString();
    }
}
