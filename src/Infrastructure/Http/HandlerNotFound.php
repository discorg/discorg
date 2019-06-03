<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use RuntimeException;
use function sprintf;

final class HandlerNotFound extends RuntimeException
{
    public static function fromPath(string $path) : self
    {
        return new self(sprintf('Handler not found for path "%s".', $path));
    }
}
