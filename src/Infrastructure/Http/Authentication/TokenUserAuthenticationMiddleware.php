<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Application\UserAuthentication\GetUserAuthenticatedByToken;
use App\Application\UserAuthentication\RenewUserSession;
use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\UserSessionToken;
use App\Infrastructure\Http\RequestTimeProvidingMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TokenUserAuthenticationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private ParseTokenFromBearerHeader $parseTokenFromBearerHeader;
    private GetUserAuthenticatedByToken $getUserAuthenticatedByToken;
    private RenewUserSession $renewUserSession;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ParseTokenFromBearerHeader $parseTokenFromBearerHeader,
        GetUserAuthenticatedByToken $getUserAuthenticatedByToken,
        RenewUserSession $renewUserSession
    ) {
        $this->responseFactory = $responseFactory;
        $this->parseTokenFromBearerHeader = $parseTokenFromBearerHeader;
        $this->getUserAuthenticatedByToken = $getUserAuthenticatedByToken;
        $this->renewUserSession = $renewUserSession;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $tokenString = $this->parseTokenFromBearerHeader->__invoke($request);
        } catch (CannotParseAuthentication $e) {
            return $this->response401();
        }

        $token = UserSessionToken::fromStoredValue($tokenString);

        $requestTime = RequestTimeProvidingMiddleware::from($request);

        try {
            $userId = $this->getUserAuthenticatedByToken->__invoke($token, $requestTime);
        } catch (UserCannotBeAuthenticated $e) {
            return $this->response401();
        }

        try {
            $this->renewUserSession->__invoke($userId, $token, $requestTime);
        } catch (UserNotFound|SessionNotFound|CannotModifySession $e) {
            return $this->responseFactory
                ->createResponse(500);
        }

        $request = $request->withAttribute(
            AuthenticatedUserId::class,
            $userId,
        );

        $request = $request->withAttribute(
            UserSessionToken::class,
            $token,
        );

        return $handler->handle($request);
    }

    private function response401() : ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(401);
    }
}
