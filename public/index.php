<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\ServiceContainer;
use Symfony\Component\Dotenv\Dotenv;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

$envFile = __DIR__ . '/../config/.env';
if (file_exists($envFile)) {
    $dotenv = new Dotenv();
    $dotenv->load($envFile);
}

$application = (new ServiceContainer())->httpApplication();
$application->run();
