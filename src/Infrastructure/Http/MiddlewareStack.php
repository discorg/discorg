<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareStack
{
    /** @var MiddlewareInterface[] */
    private array $stack;

    private function __construct()
    {
    }

    public static function fromArray(MiddlewareInterface ...$stack) : self
    {
        $instance = new self();
        $instance->stack = $stack;

        return $instance;
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function toArray() : array
    {
        return $this->stack;
    }
}
