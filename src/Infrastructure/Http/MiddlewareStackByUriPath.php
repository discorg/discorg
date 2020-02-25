<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Psr\Http\Message\UriInterface;
use function strlen;
use function substr;

final class MiddlewareStackByUriPath
{
    private string $uriPath;
    private MiddlewareStack $stack;

    private function __construct()
    {
    }

    public static function from(string $uriPath, MiddlewareStack $stack) : self
    {
        $instance = new self();
        $instance->uriPath = $uriPath;
        $instance->stack = $stack;

        return $instance;
    }

    public function uriMatches(UriInterface $uri) : bool
    {
        $actualUriPath = $uri->getPath();

        return substr($actualUriPath, 0, strlen($this->uriPath)) === $this->uriPath;
    }

    public function getStack() : MiddlewareStack
    {
        return $this->stack;
    }
}
