<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\UserSessionToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UserAuthenticationProvidingMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $handler->handle($request);
    }

    public static function userIdFrom(ServerRequestInterface $request) : AuthenticatedUserId
    {
        return $request->getAttribute(AuthenticatedUserId::class);
    }

    public static function userSessionTokenFrom(ServerRequestInterface $request) : UserSessionToken
    {
        return $request->getAttribute(UserSessionToken::class);
    }
}
