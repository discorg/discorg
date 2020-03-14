<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Application\UserAuthentication\IsUserAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\UserCredentials;
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

final class BasicUserAuthenticationMiddleware implements MiddlewareInterface
{
    private SpecFinder $specFinder;
    private ResponseFactoryInterface $responseFactory;
    private IsUserAuthenticated $isUserAuthenticated;

    public function __construct(
        SpecFinder $specFinder,
        ResponseFactoryInterface $responseFactory,
        IsUserAuthenticated $isUserAuthenticated
    ) {
        $this->specFinder = $specFinder;
        $this->responseFactory = $responseFactory;
        $this->isUserAuthenticated = $isUserAuthenticated;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $operationAddress = $request->getAttribute(OperationAddress::class);
        assert($operationAddress instanceof OperationAddress);

        try {
            $securitySpecs = $this->specFinder->findSecuritySpecs($operationAddress);
        } catch (NoPath $e) {
            throw new LogicException($e->getMessage());
        }

        if ($securitySpecs === []) {
            return $handler->handle($request);
        }

        $securitySchemesSpecs = $this->specFinder->findSecuritySchemesSpecs();

        foreach ($securitySpecs as $securitySpec) {
            foreach ($securitySpec->getSerializableData() as $securitySchemeName => $scopes) {
                $securityScheme = $securitySchemesSpecs[$securitySchemeName];
                if ($securityScheme->type !== 'http' || $securityScheme->scheme !== 'basic') {
                    return $handler->handle($request);
                }
            }
        }

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

        if (! $this->isUserAuthenticated->__invoke($credentials)) {
            return $this->responseFactory->createResponse(401)
                ->withHeader('WWW-Authenticate', 'Basic realm="Login"');
        }

        $request = $request->withAttribute(
            AuthenticatedUserIdentifier::class,
            AuthenticatedUserIdentifier::fromEmailAddress($credentials->emailAddress()),
        );

        return $handler->handle($request);
    }
}
