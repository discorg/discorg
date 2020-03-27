<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Application\UserAuthentication\GetUserAuthenticatedByToken;
use App\Application\UserAuthentication\RenewUserSession;
use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\UserSessionToken;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;

final class TokenUserAuthenticationMiddleware implements MiddlewareInterface
{
    private SpecFinder $specFinder;
    private ResponseFactoryInterface $responseFactory;
    private GetUserAuthenticatedByToken $getUserAuthenticatedByToken;
    private RenewUserSession $renewUserSession;

    public function __construct(
        SpecFinder $specFinder,
        ResponseFactoryInterface $responseFactory,
        GetUserAuthenticatedByToken $getUserAuthenticatedByToken,
        RenewUserSession $renewUserSession
    ) {
        $this->specFinder = $specFinder;
        $this->responseFactory = $responseFactory;
        $this->getUserAuthenticatedByToken = $getUserAuthenticatedByToken;
        $this->renewUserSession = $renewUserSession;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if (! $this->isAuthenticationRequired($request)) {
            return $handler->handle($request);
        }

        try {
            $authentication = TokenAuthentication::fromRequestHeader($request);
        } catch (CannotParseAuthentication $e) {
            return $this->response401();
        }

        $token = UserSessionToken::fromStoredValue($authentication->token());

        try {
            $userIdentifier = $this->getUserAuthenticatedByToken->__invoke($token);
        } catch (UserCannotBeAuthenticated $e) {
            return $this->response401();
        }

        try {
            $this->renewUserSession->__invoke($userIdentifier, $token);
        } catch (UserNotFound|SessionNotFound|CannotModifySession $e) {
            return $this->responseFactory
                ->createResponse(500);
        }

        $request = $request->withAttribute(
            AuthenticatedUserIdentifier::class,
            $userIdentifier,
        );

        $request = $request->withAttribute(
            UserSessionToken::class,
            $token,
        );

        return $handler->handle($request);
    }

    private function isAuthenticationRequired(ServerRequestInterface $request) : bool
    {
        $operationAddress = $request->getAttribute(OperationAddress::class);
        assert($operationAddress instanceof OperationAddress);

        try {
            $securitySpecs = $this->specFinder->findSecuritySpecs($operationAddress);
        } catch (NoPath $e) {
            throw new LogicException($e->getMessage());
        }

        $securitySchemesSpecs = $this->specFinder->findSecuritySchemesSpecs();

        foreach ($securitySpecs as $securitySpec) {
            foreach ($securitySpec->getSerializableData() as $securitySchemeName => $scopes) {
                $securityScheme = $securitySchemesSpecs[$securitySchemeName];
                if ($securityScheme->type === 'http' && $securityScheme->scheme === 'bearer') {
                    return true;
                }
            }
        }

        return false;
    }

    private function response401() : ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(401);
    }
}
