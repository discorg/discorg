<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function array_key_exists;
use function json_decode;
use function json_encode;

final class CreateSession implements RequestHandlerInterface
{
    private StreamFactoryInterface $streamFactory;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(StreamFactoryInterface $streamFactory, ResponseFactoryInterface $responseFactory)
    {
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $requestPayloadString = (string) $request->getBody();
        $requestPayload = json_decode($requestPayloadString);
        if ($requestPayload === null
            || ! array_key_exists('email', $requestPayload)
            || ! array_key_exists('password', $requestPayload)) {
            return $response = $this->responseFactory->createResponse(400);
        }

        $token = '12345';

        $responsePayload = json_encode(['token' => $token]);
        if ($responsePayload === false) {
            throw new LogicException('Json encode failed.');
        }

        $responseBodyAsStream = $this->streamFactory->createStream($responsePayload);

        return $response = $this->responseFactory
            ->createResponse(200)
            ->withBody($responseBodyAsStream);
    }
}
