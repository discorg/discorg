<?php declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Application\Application;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Tracy\Debugger;

const ROOT_DIRECTORY = __DIR__ . '/../';

Debugger::enable();
Debugger::$maxDepth = 7;

$envFile = ROOT_DIRECTORY . 'config/.env';
if (file_exists($envFile)) {
	$dotenv = new Dotenv();
	$dotenv->load($envFile);
}

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(ROOT_DIRECTORY));
$loader->load(ROOT_DIRECTORY . 'src/Application/config.yaml');

$container->setParameter('wwwUrl', 'http://' . $_SERVER['HTTP_HOST']);

$resolveEnvPlaceholders = true;
$container->compile($resolveEnvPlaceholders);

/** @var Application $application */
$application = $container->get(Application::class);
$application->run($container);
