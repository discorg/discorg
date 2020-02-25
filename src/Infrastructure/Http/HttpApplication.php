<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Relay\Relay;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use function sprintf;

final class HttpApplication implements RequestHandlerInterface
{
    /** @var MiddlewareStackByUriPath[] */
    private array $middlewareStacksByUriPath;

    public function __construct(MiddlewareStackByUriPath ...$middlewareStacksByUriPath)
    {
        $this->middlewareStacksByUriPath = $middlewareStacksByUriPath;
    }

    public function run() : void
    {
        $psr17Factory = new Psr17Factory();

        $creator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        $request = $creator->fromGlobals();

        $response = $this->handle($request);

        (new SapiEmitter())->emit($response);
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $uri = $request->getUri();

        foreach ($this->middlewareStacksByUriPath as $middlewareStackByUriPath) {
            if ($middlewareStackByUriPath->uriMatches($uri)) {
                $relay = new Relay($middlewareStackByUriPath->getStack()->toArray());

                return $relay->handle($request);
            }
        }

        throw new LogicException(sprintf('No matching middleware stack found for uri "%s".', (string) $uri));
    }
}
