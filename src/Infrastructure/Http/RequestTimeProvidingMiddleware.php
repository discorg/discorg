<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Clock;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestTimeProvidingMiddleware implements MiddlewareInterface
{
    private Clock $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $request = $request->withAttribute(
            self::class,
            $this->clock->getCurrentTime()
        );

        return $handler->handle($request);
    }

    public static function from(ServerRequestInterface $request) : DateTimeImmutable
    {
        return $request->getAttribute(self::class);
    }
}
