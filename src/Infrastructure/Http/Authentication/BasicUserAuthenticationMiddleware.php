<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Application\UserAuthentication\GetUserAuthenticatedByCredentials;
use App\Domain\UserAuthentication\Aggregate\UserCannotBeAuthenticated;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
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
    private GetUserAuthenticatedByCredentials $getUserAuthenticatedByCredentials;

    public function __construct(
        SpecFinder $specFinder,
        ResponseFactoryInterface $responseFactory,
        GetUserAuthenticatedByCredentials $getUserAuthenticatedByCredentials
    ) {
        $this->specFinder = $specFinder;
        $this->responseFactory = $responseFactory;
        $this->getUserAuthenticatedByCredentials = $getUserAuthenticatedByCredentials;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if (! $this->isAuthenticationRequired($request)) {
            return $handler->handle($request);
        }

        try {
            $authentication = BasicAuthentication::fromRequestHeader($request);
        } catch (CannotParseAuthentication $e) {
            return $this->response401();
        }

        try {
            $userId = $this->getUserAuthenticatedByCredentials->__invoke(
                $authentication->username(),
                $authentication->password(),
            );
        } catch (UserCannotBeAuthenticated $e) {
            return $this->response401();
        }

        $request = $request->withAttribute(
            AuthenticatedUserIdentifier::class,
            $userId,
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
                if ($securityScheme->type === 'http' && $securityScheme->scheme === 'basic') {
                    return true;
                }
            }
        }

        return false;
    }

    private function response401() : ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(401)
            ->withHeader('WWW-Authenticate', 'Basic realm="Login"');
    }
}
