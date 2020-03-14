<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

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
}
