<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonRequestValidatingMiddleware implements MiddlewareInterface
{
    private ServerRequestValidator $validator;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ServerRequestValidator $serverRequestValidator,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->validator = $serverRequestValidator;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $this->validator->validate($request);
        } catch (ValidationFailed $e) {
            return $this->responseFactory->createResponse(400, $e->getMessage());
        }

        return $handler->handle($request);
    }
}
