<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use App\Application\UserAuthentication\ReadModel\UserNotFound;
use App\Infrastructure\Http\Authentication\UserAuthenticationProvidingMiddleware;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function json_encode;

final class GetUser implements RequestHandlerInterface
{
    private \App\Application\UserAuthentication\ReadModel\GetUser $getUser;
    private StreamFactoryInterface $streamFactory;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        \App\Application\UserAuthentication\ReadModel\GetUser $getUser,
        StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->getUser = $getUser;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $userId = UserAuthenticationProvidingMiddleware::userIdFrom($request);

        try {
            $user = $this->getUser->__invoke($userId);
        } catch (UserNotFound $e) {
            return $this->responseFactory
                ->createResponse(404)
                ->withHeader('Content-Type', 'application/json');
        }

        $responsePayload = json_encode(['email' => $user->emailAddress()]);
        if ($responsePayload === false) {
            throw new LogicException('Json encode failed.');
        }

        $responseBodyAsStream = $this->streamFactory->createStream($responsePayload);

        return $this->responseFactory
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($responseBodyAsStream);
    }
}
