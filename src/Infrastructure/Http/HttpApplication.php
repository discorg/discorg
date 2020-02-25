<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Relay\Relay;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

final class HttpApplication implements RequestHandlerInterface
{
    private MiddlewareStack $middlewareStack;

    public function __construct(MiddlewareStack $middlewareStack)
    {
        $this->middlewareStack = $middlewareStack;
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
        $relay = new Relay($this->middlewareStack->toArray());

        return $relay->handle($request);
    }
}
