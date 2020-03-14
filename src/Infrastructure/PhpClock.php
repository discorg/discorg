<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Clock;
use DateTimeImmutable;

final class PhpClock implements Clock
{
    public function getCurrentTime() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
