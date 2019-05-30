<?php

declare(strict_types=1);

namespace App\Infrastructure\Application;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use function sprintf;
use function ucfirst;

class ActionResolver
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve(ServerRequestInterface $request) : Action
    {
        $queryParameters = $request->getQueryParams();

        $actionName = $queryParameters['action'] ?? 'home';

        $actionServiceName = sprintf('App\Infrastructure\Actions\%sAction', ucfirst($actionName));

        if (! $this->container->has($actionServiceName)) {
            throw new RuntimeException('Action not found.');
        }

        /** @var Action $action */
        $action = $this->container->get($actionServiceName);

        return $action;
    }
}
