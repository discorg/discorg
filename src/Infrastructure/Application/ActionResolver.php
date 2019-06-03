<?php

declare(strict_types=1);

namespace App\Infrastructure\Application;

use App\Infrastructure\ServiceContainer;
use Psr\Http\Message\ServerRequestInterface;
use function sprintf;
use function ucfirst;

class ActionResolver
{
    /** @var ServiceContainer */
    private $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function resolve(ServerRequestInterface $request) : Action
    {
        $queryParameters = $request->getQueryParams();

        $actionName = $queryParameters['action'] ?? 'home';

        $actionServiceName = sprintf('App\Infrastructure\Actions\%sAction', ucfirst($actionName));

        /** @var Action $action */
        $action = $this->container->getAction($actionServiceName);

        return $action;
    }
}
