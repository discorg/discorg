<?php declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Application\HttpApplication;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Tracy\Debugger;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

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

$resolveEnvPlaceholders = true;
$container->compile($resolveEnvPlaceholders);

$psr17Factory = new Psr17Factory();

$creator = new ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);

$request = $creator->fromGlobals();

// fix Nyholm\Psr7Server\ServerRequestCreator bug with duplicated port in URI
$uri = $request->getUri();
$host = $uri->getHost();
// remove port from host
$host = preg_replace('#:[0-9]+$#', '', $host);
$uri = $uri->withHost($host);
$request = $request->withUri($uri);

/** @var HttpApplication $application */
$application = $container->get(HttpApplication::class);
$response = $application->run($request, $container);

(new SapiEmitter())->emit($response);
