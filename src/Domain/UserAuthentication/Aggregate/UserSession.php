<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;

final class UserSession
{
    private UserSessionToken $token;
    private DateTimeImmutable $startedAt;

    private function __construct(UserSessionToken $token, DateTimeImmutable $startedAt)
    {
        $this->token = $token;
        $this->startedAt = $startedAt;
    }

    public static function create(UserSessionToken $token, DateTimeImmutable $at) : self
    {
        return new self($token, $at);
    }

    /**
     * Satisfy phpstan.
     *
     * @return mixed[]
     */
    public function toArray() : array
    {
        return [
            'token' => $this->token->toString(),
            'startedAt' => $this->startedAt,
        ];
    }
}
