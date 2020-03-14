<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Api;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\PathFinder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function count;
use function reset;

final class ApiOperationFindingMiddleware implements MiddlewareInterface
{
    private OpenApi $schema;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(OpenApi $schema, ResponseFactoryInterface $responseFactory)
    {
        $this->schema = $schema;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $pathFinder = new PathFinder($this->schema, $request->getUri(), $request->getMethod());
        $operationAddresses = $pathFinder->search();
        if (count($operationAddresses) !== 1) {
            return $this->responseFactory->createResponse(404, 'API operation cannot be resolved.');
        }

        $operationAddress = reset($operationAddresses);
        $request = $request->withAttribute(OperationAddress::class, $operationAddress);

        return $handler->handle($request);
    }
}
