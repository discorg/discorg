<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Clock;
use DateTimeImmutable;

final class FreezableClock implements Clock
{
    private Clock $delegate;
    private DateTimeImmutable $frozenAt;

    public function __construct(Clock $delegate)
    {
        $this->delegate = $delegate;
    }

    public function getCurrentTime() : DateTimeImmutable
    {
        return $this->frozenAt ?? $this->delegate->getCurrentTime();
    }

    public function freeze(DateTimeImmutable $at) : void
    {
        $this->frozenAt = $at;
    }
}
