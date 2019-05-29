<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Application\ContainerFactory;
use Bouda\SpotifyAlbumTagger\Application\HttpApplication;
use Symfony\Component\Dotenv\Dotenv;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

$envFile = __DIR__ . '/../config/.env';
if (file_exists($envFile)) {
    $dotenv = new Dotenv();
    $dotenv->load($envFile);
}

$containerFactory = new ContainerFactory();
$container = $containerFactory->create(__DIR__ . '/../');

/** @var HttpApplication $application */
$application = $container->get(HttpApplication::class);
$application->run();
