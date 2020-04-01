<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\UserSessionToken;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;
use function sprintf;

final class UserAuthenticationProvidingMiddleware implements MiddlewareInterface
{
    private const AUTHENTICATION_TYPE_NONE = 'none';
    private const AUTHENTICATION_TYPE_BASIC = 'basic';
    private const AUTHENTICATION_TYPE_TOKEN = 'bearer';

    private SpecFinder $specFinder;
    private BasicUserAuthenticationMiddleware $basicUserAuthenticationMiddleware;
    private TokenUserAuthenticationMiddleware $tokenUserAuthenticationMiddleware;

    public function __construct(
        SpecFinder $specFinder,
        BasicUserAuthenticationMiddleware $basicUserAuthenticationMiddleware,
        TokenUserAuthenticationMiddleware $tokenUserAuthenticationMiddleware
    ) {
        $this->specFinder = $specFinder;
        $this->basicUserAuthenticationMiddleware = $basicUserAuthenticationMiddleware;
        $this->tokenUserAuthenticationMiddleware = $tokenUserAuthenticationMiddleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $type = $this->authenticationTypeRequired($request);

        if ($type === self::AUTHENTICATION_TYPE_NONE) {
            return $handler->handle($request);
        }

        if ($type === self::AUTHENTICATION_TYPE_BASIC) {
            return $this->basicUserAuthenticationMiddleware->process($request, $handler);
        }

        if ($type === self::AUTHENTICATION_TYPE_TOKEN) {
            return $this->tokenUserAuthenticationMiddleware->process($request, $handler);
        }

        throw new LogicException(sprintf('Unknown authentication type: %s', $type));
    }

    private function authenticationTypeRequired(ServerRequestInterface $request) : string
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
                if ($securityScheme->type === 'http') {
                    return $securityScheme->scheme;
                }
            }
        }

        return self::AUTHENTICATION_TYPE_NONE;
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
