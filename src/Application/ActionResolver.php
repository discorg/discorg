<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Application;

use Psr\Container\ContainerInterface;
use RuntimeException;
use function preg_match;
use function sprintf;
use function ucfirst;

class ActionResolver
{
    public function resolve(ContainerInterface $container) : Action
    {
        $requestQuery = $_SERVER['QUERY_STRING'] ?? null;
        if ($requestQuery === null) {
            $requestQuery = '?action=home';
        }
        preg_match('#action=([^&]*)#', $requestQuery, $matches);

        if (! isset($matches[1])) {
            throw new RuntimeException('Action not set.');
        }

        $actionName = $matches[1];

        $actionServiceName = sprintf('Bouda\SpotifyAlbumTagger\Actions\%sAction', ucfirst($actionName));

        if (! $container->has($actionServiceName)) {
            throw new RuntimeException('Action not found.');
        }

        /** @var Action $action */
        $action = $container->get($actionServiceName);

        return $action;
    }
}
