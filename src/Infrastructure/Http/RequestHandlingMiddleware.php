<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlingMiddleware implements MiddlewareInterface
{
    /** @var HandlerFactoryCollection */
    private $collection;

    public function __construct(HandlerFactoryCollection $collection)
    {
        $this->collection = $collection;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $action = HttpActionIdentifier::fromRequest($request);

        try {
            $handlerFactory = $this->collection->getFactory($action);
        } catch (HandlerNotFound $exception) {
            return (new Psr17Factory())->createResponse(404);
        }

        /** @var RequestHandlerInterface $handler */
        $handler = $handlerFactory();

        return $handler->handle($request);
    }
}
