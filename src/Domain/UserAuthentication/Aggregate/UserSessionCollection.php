<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;
use LogicException;
use function array_filter;
use function count;
use function reset;

final class UserSessionCollection
{
    /** @var UserSession[] */
    private array $sessions = [];

    /**
     * @param UserSession[] $sessions
     */
    private function __construct(array $sessions)
    {
        $this->sessions = $sessions;
    }

    public static function empty() : self
    {
        return new self([]);
    }

    public function with(UserSession $session) : self
    {
        return new self([...$this->sessions, $session]);
    }

    public function hasValid(UserSessionToken $token, DateTimeImmutable $at) : bool
    {
        return array_filter(
            $this->sessions,
            static fn (UserSession $session) => $session->matches($token) && $session->isValid($at),
        ) !== [];
    }

    /**
     * @throws SessionNotFound
     */
    public function get(UserSessionToken $token) : UserSession
    {
        $result = array_filter(
            $this->sessions,
            static fn(UserSession $session) => $session->matches($token),
        );

        if ($result === []) {
            throw SessionNotFound::byToken($token);
        }

        if (count($result) !== 1) {
            throw new LogicException('More than one session with the same token found.');
        }

        return reset($result);
    }
}
