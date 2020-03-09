<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateUser implements RequestHandlerInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json');
    }
}
