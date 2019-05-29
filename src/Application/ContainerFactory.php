<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Application;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ContainerFactory
{
    public function create(string $rootDirectory) : Container
    {
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator($rootDirectory));
        $loader->load($rootDirectory . 'src/Application/config.yaml');

        $resolveEnvPlaceholders = true;
        $container->compile($resolveEnvPlaceholders);

        return $container;
    }
}
