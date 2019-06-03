<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Http\Actions\Albums\GetAlbums;
use App\Infrastructure\Http\Actions\Get;
use App\Infrastructure\ServiceContainer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class HandlerResolver
{
    /** @var ServiceContainer */
    private $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @throws HandlerNotFound
     * @throws RuntimeException
     */
    public function resolve(ServerRequestInterface $request) : RequestHandlerInterface
    {
        $path = $request->getUri()->getPath();

        switch ($path) {
            case '/':
                return $this->container->getHttpHandler(Get::class);
            case '/albums':
                return $this->container->getHttpHandler(GetAlbums::class);
            default:
                throw HandlerNotFound::fromPath($path);
        }
    }
}
