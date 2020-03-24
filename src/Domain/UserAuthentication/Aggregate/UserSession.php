<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;
use function sprintf;

final class UserSession
{
    private const VALIDITY_IN_HOURS = 24;

    private UserSessionToken $token;
    private DateTimeImmutable $startedAt;
    private DateTimeImmutable $renewedAt;
    private ?DateTimeImmutable $endedAt = null;

    private function __construct(UserSessionToken $token, DateTimeImmutable $startedAt)
    {
        $this->token = $token;
        $this->startedAt = $startedAt;
        $this->renewedAt = $startedAt;
    }

    public static function create(UserSessionToken $token, DateTimeImmutable $at) : self
    {
        return new self($token, $at);
    }

    /**
     * @throws CannotModifySession
     */
    public function renew(DateTimeImmutable $at) : void
    {
        if (! $this->isValid($at)) {
            throw CannotModifySession::alreadyExpired();
        }

        $this->renewedAt = $at;
    }

    /**
     * @throws CannotModifySession
     */
    public function end(DateTimeImmutable $at) : void
    {
        if (! $this->isValid($at)) {
            throw CannotModifySession::alreadyExpired();
        }

        $this->endedAt = $at;
    }

    public function matches(UserSessionToken $token) : bool
    {
        return $this->token->equals($token);
    }

    public function isValid(DateTimeImmutable $at) : bool
    {
        return $this->endedAt === null
            && $this->renewedAt >= $at->modify(sprintf('- %d hours', self::VALIDITY_IN_HOURS));
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
            'endedAt' => $this->endedAt,
        ];
    }
}
