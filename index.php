<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Actions\Action;
use Bouda\SpotifyAlbumTagger\Spotify\Session\InitializableSpotifySessionManager;
use Bouda\SpotifyAlbumTagger\User\InitializableUserSessionManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

$envFile = __DIR__ . '/config/.env';
if (file_exists($envFile)) {
	$dotenv = new Dotenv();
	$dotenv->load($envFile);
}

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('src/Spotify/config.yaml');
$loader->load('src/User/config.yaml');
$loader->load('actions/config.yaml');

$container->setParameter('wwwUrl', 'http://' . $_SERVER['HTTP_HOST']);

$resolveEnvPlaceholders = true;
$container->compile($resolveEnvPlaceholders);


/** @var InitializableUserSessionManager $userSessionManager */
$userSessionManager = $container->get(InitializableUserSessionManager::class);
$initializedUserSessionManager = $userSessionManager->initialize();
$userSession = $initializedUserSessionManager->getSession();

/** @var InitializableSpotifySessionManager $spotifySessionManager */
$spotifySessionManager = $container->get(InitializableSpotifySessionManager::class);
$spotifySessionManager = $spotifySessionManager->initialize();
$spotifySession = $spotifySessionManager->getSession();

$requestQuery = $_SERVER['QUERY_STRING'];
if ($requestQuery === null) {
	$requestQuery = '?action=home';
}
preg_match('#action=([^&]*)#', $requestQuery, $matches);

if (isset($matches[1])) {
	$actionName = $matches[1];
} else {
	throw new RuntimeException('Action not set.');
}

$actionServiceName = sprintf('Bouda\SpotifyAlbumTagger\Actions\%sAction', ucfirst($actionName));

if (!$container->has($actionServiceName)) {
	throw new RuntimeException('Action not found.');
}

/** @var Action $action */
$action = $container->get($actionServiceName);
$action();
