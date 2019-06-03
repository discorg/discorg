<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\User\UserSession;
use App\Infrastructure\User\UserSessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UserSessionMiddleware implements MiddlewareInterface
{
    /** @var UserSessionManager */
    private $userSessionManager;

    public function __construct(UserSessionManager $userSessionManager)
    {
        $this->userSessionManager = $userSessionManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $userSession = $this->userSessionManager->initialize($request);

        $request = $request->withAttribute(UserSession::class, $userSession);

        $response = $handler->handle($request);

        $response = $this->userSessionManager->saveSession($response, $userSession);

        return $response;
    }
}
