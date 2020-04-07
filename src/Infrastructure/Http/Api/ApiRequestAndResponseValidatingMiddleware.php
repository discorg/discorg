<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Api;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class ApiRequestAndResponseValidatingMiddleware implements MiddlewareInterface
{
    private ServerRequestValidator $requestValidator;
    private ResponseValidator $responseValidator;
    private ResponseFactoryInterface $responseFactory;
    private LoggerInterface $logger;

    public function __construct(
        ServerRequestValidator $serverRequestValidator,
        ResponseValidator $responseValidator,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger
    ) {
        $this->requestValidator = $serverRequestValidator;
        $this->responseValidator = $responseValidator;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $operation = $this->requestValidator->validate($request);
        } catch (ValidationFailed $e) {
            $reason = $e->getMessage();

            $previous = $e->getPrevious();
            if ($previous !== null) {
                $reason = $previous->getMessage();
            }

            return $this->responseFactory->createResponse(400, $reason);
        }

        $response = $handler->handle($request);

        try {
            $this->responseValidator->validate($operation, $response);
        } catch (ValidationFailed $e) {
            $this->logger->error($e->getMessage());

            return $this->responseFactory->createResponse(500);
        }

        return $response;
    }
}
