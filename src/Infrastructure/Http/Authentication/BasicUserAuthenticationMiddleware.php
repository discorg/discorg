<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Application\UserAuthentication\IsUserAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\UserCredentials;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BasicUserAuthenticationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private IsUserAuthenticated $isUserAuthenticated;

    public function __construct(ResponseFactoryInterface $responseFactory, IsUserAuthenticated $isUserAuthenticated)
    {
        $this->responseFactory = $responseFactory;
        $this->isUserAuthenticated = $isUserAuthenticated;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $authentication = BasicAuthentication::fromRequestHeader($request);
        } catch (CannotParseAuthentication $e) {
            return $this->responseFactory->createResponse(401)
                ->withHeader('WWW-Authenticate', 'Basic realm="Login"');
        }

        $credentials = UserCredentials::fromStrings(
            $authentication->username(),
            $authentication->password(),
        );

        $this->isUserAuthenticated->__invoke($credentials);

        $request = $request->withAttribute(
            AuthenticatedUserIdentifier::class,
            AuthenticatedUserIdentifier::fromEmailAddress($credentials->emailAddress()),
        );

        return $handler->handle($request);
    }
}
