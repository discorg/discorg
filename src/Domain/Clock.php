<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

interface Clock
{
    public function getCurrentTime() : DateTimeImmutable;
}
