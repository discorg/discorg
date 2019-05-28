<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Application\ContainerFactory;
use Bouda\SpotifyAlbumTagger\Application\HttpApplication;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

$containerFactory = new ContainerFactory();
$container = $containerFactory->create(__DIR__ . '/../');

/** @var HttpApplication $application */
$application = $container->get(HttpApplication::class);
$application->run();
