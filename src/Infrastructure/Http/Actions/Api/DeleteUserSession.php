<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use App\Application\UserAuthentication\EndUserSession;
use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\UserSessionToken;
use Assert\Assertion;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DeleteUserSession implements RequestHandlerInterface
{
    private EndUserSession $endUserSession;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        EndUserSession $endUserSession,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->endUserSession = $endUserSession;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $token = $request->getAttribute(UserSessionToken::class);
        Assertion::isInstanceOf($token, UserSessionToken::class);

        try {
            // TODO: pass frozen time
            $this->endUserSession->__invoke($token);
        } catch (UserNotFound|SessionNotFound|CannotModifySession $e) {
            throw new LogicException('User should have already been authenticated in this context.', 0, $e);
        }

        return $this->responseFactory
            ->createResponse(204)
            ->withHeader('Content-Type', 'application/json');
    }
}
