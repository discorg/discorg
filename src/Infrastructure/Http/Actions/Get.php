<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Get implements RequestHandlerInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $responseBody = (new Psr17Factory())->createStream('Hello world');

        return $response = $this->responseFactory
            ->createResponse(200)
            ->withBody($responseBody);
    }
}
