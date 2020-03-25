<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Api;

use App\Application\UserAuthentication\RegisterUser;
use App\Domain\UserAuthentication\Aggregate\CannotRegisterUser;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function json_decode;

final class CreateUser implements RequestHandlerInterface
{
    private ResponseFactoryInterface $responseFactory;
    private RegisterUser $registerUser;

    public function __construct(RegisterUser $registerUser, ResponseFactoryInterface $responseFactory)
    {
        $this->registerUser = $registerUser;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $requestPayloadString = (string) $request->getBody();
        $requestPayload = json_decode($requestPayloadString, true);

        try {
            $this->registerUser->__invoke(
                EmailAddress::fromString($requestPayload['email']),
                PlaintextUserPassword::fromString($requestPayload['password']),
            );
        } catch (CannotRegisterUser $e) {
            return $this->responseFactory
                ->createResponse(400, $e->getMessage());
        }

        return $this->responseFactory
            ->createResponse(204)
            ->withHeader('Content-Type', 'application/json');
    }
}
