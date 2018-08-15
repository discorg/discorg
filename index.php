<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Spotify\Session\InitializableSpotifySessionManager;
use Bouda\SpotifyAlbumTagger\Spotify\SpotifyUserLibraryFacade;
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


/** @var SpotifyUserLibraryFacade $spotifyUserLibrary */
$spotifyUserLibrary = $container->get(SpotifyUserLibraryFacade::class);

foreach ($spotifyUserLibrary->getAlbums(5) as $album) {
	$album = $album['album'];
	$uri = $album['uri'];
	$imageUrl = $album['images'][1]['url'];
	$title = $album['artists'][0]['name'] . ' - ' . substr($album['release_date'], 0, 4) . ' - ' . $album['name'];
	echo sprintf('<a href="%s">%s</a><br>', $uri, $title);
}
