<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ApiRequestAndResponseValidatingMiddleware implements MiddlewareInterface
{
    private ServerRequestValidator $requestValidator;
    private ResponseValidator $responseValidator;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ServerRequestValidator $serverRequestValidator,
        ResponseValidator $responseValidator,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->requestValidator = $serverRequestValidator;
        $this->responseValidator = $responseValidator;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $operation = $this->requestValidator->validate($request);
        } catch (ValidationFailed $e) {
            return $this->responseFactory->createResponse(400, $e->getMessage());
        }

        $response = $handler->handle($request);

        try {
            $this->responseValidator->validate($operation, $response);
        } catch (ValidationFailed $e) {
            return $this->responseFactory->createResponse(500, $e->getMessage());
        }

        return $response;
    }
}
