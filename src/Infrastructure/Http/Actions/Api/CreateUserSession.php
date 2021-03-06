<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use App\Application\UserAuthentication\StartUserSession;
use App\Domain\UserAuthentication\Aggregate\CannotStartUserSession;
use App\Domain\UserAuthentication\UserSessionToken;
use App\Infrastructure\Http\Authentication\UserAuthenticationProvidingMiddleware;
use App\Infrastructure\Http\RequestTimeProvidingMiddleware;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function json_encode;

final class CreateUserSession implements RequestHandlerInterface
{
    private StartUserSession $startUserSession;
    private StreamFactoryInterface $streamFactory;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        StartUserSession $startUserSession,
        StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->startUserSession = $startUserSession;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $userId = UserAuthenticationProvidingMiddleware::userIdFrom($request);
        $token = UserSessionToken::generate();
        $requestTime = RequestTimeProvidingMiddleware::from($request);

        try {
            $this->startUserSession->__invoke($userId, $token, $requestTime);
        } catch (CannotStartUserSession $e) {
            $this->responseFactory
                ->createResponse(401)
                ->withHeader('Content-Type', 'application/json');
        }

        $responsePayload = json_encode(['token' => $token->toString()]);
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
