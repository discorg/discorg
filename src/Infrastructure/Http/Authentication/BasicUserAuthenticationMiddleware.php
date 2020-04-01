<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Application\UserAuthentication\GetUserAuthenticatedByCredentials;
use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserId;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class BasicUserAuthenticationMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private ParseCredentialsFromBasicAuthenticationHeader $parseCredentialsFromBasicAuthenticationHeader;
    private GetUserAuthenticatedByCredentials $getUserAuthenticatedByCredentials;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ParseCredentialsFromBasicAuthenticationHeader $parseCredentialsFromBasicAuthenticationHeader,
        GetUserAuthenticatedByCredentials $getUserAuthenticatedByCredentials
    ) {
        $this->responseFactory = $responseFactory;
        $this->parseCredentialsFromBasicAuthenticationHeader = $parseCredentialsFromBasicAuthenticationHeader;
        $this->getUserAuthenticatedByCredentials = $getUserAuthenticatedByCredentials;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $credentials = $this->parseCredentialsFromBasicAuthenticationHeader->__invoke($request);
        } catch (CannotParseAuthentication $e) {
            return $this->response401();
        }

        try {
            $userId = $this->getUserAuthenticatedByCredentials->__invoke($credentials);
        } catch (UserCannotBeAuthenticated $e) {
            return $this->response401();
        }

        $request = $request->withAttribute(
            AuthenticatedUserId::class,
            $userId,
        );

        return $handler->handle($request);
    }

    private function response401() : ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(401)
            ->withHeader('WWW-Authenticate', 'Basic realm="Login"');
    }
}
