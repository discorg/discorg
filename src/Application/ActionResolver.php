<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Application;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use function sprintf;
use function ucfirst;

class ActionResolver
{
    public function resolve(ServerRequestInterface $request, ContainerInterface $container) : Action
    {
        $queryParameters = $request->getQueryParams();

        $actionName = $queryParameters['action'] ?? 'home';

        $actionServiceName = sprintf('Bouda\SpotifyAlbumTagger\Actions\%sAction', ucfirst($actionName));

        if (! $container->has($actionServiceName)) {
            throw new RuntimeException('Action not found.');
        }

        /** @var Action $action */
        $action = $container->get($actionServiceName);

        return $action;
    }
}
