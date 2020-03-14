<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use App\Application\StartUserSession;
use App\Domain\UserAuthentication\Aggregate\CannotStartUserSession;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\UserSessionToken;
use Assert\Assertion;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function json_encode;

final class CreateSession implements RequestHandlerInterface
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
        $userIdentifier = $request->getAttribute(AuthenticatedUserIdentifier::class);
        Assertion::isInstanceOf($userIdentifier, AuthenticatedUserIdentifier::class);
        $token = UserSessionToken::generate();

        try {
            $this->startUserSession->__invoke($userIdentifier, $token);
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
