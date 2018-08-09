<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';

use Bouda\SpotifyAlbumTagger\Spotify\Session\SpotifySessionFactory;
use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

$envFile = __DIR__.'/config/.env';
if (file_exists($envFile)) {
	$dotenv = new Dotenv();
	$dotenv->load($envFile);
}

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$loader->load('src/Spotify/config.yaml');

$container->setParameter('wwwUrl', 'http://' . $_SERVER['HTTP_HOST']);

$resolveEnvPlaceholders = true;
$container->compile($resolveEnvPlaceholders);

/** @var SpotifySessionFactory $spotifySessionFactory */
$spotifySessionFactory = $container->get('spotifySessionFactory');


const CACHE_DIR = __DIR__ . '/var/cache';

const ACCESS_TOKEN_FILE = CACHE_DIR . '/access-token';
const REFRESH_TOKEN_FILE = CACHE_DIR . '/refresh-token';


if (!file_exists(ACCESS_TOKEN_FILE) && !isset($_GET['code'])) {
	$spotifySession = $spotifySessionFactory->createAuthorizable();

	$url = $spotifySession->getAuthorizeUrl();

	echo 'Redirecting to spotify.';

	header('refresh:1;' . $url);
	die();

} elseif (isset($_GET['code'])) {
	$spotifySession = $spotifySessionFactory->createAuthorizable();

	$code = $_GET['code'];
	$session = $spotifySession->authorize($code);

	file_put_contents(ACCESS_TOKEN_FILE, $session->getAccessToken());
	file_put_contents(REFRESH_TOKEN_FILE, $session->getRefreshToken());

	header('refresh:1;index.php');
	die();

} elseif (file_exists(ACCESS_TOKEN_FILE)) {
	$accessToken = file_get_contents(ACCESS_TOKEN_FILE);
	$refreshToken = file_get_contents(REFRESH_TOKEN_FILE);
	$spotifySession = $spotifySessionFactory->createAuthorized($accessToken, $refreshToken);

	$api = new SpotifyWebAPI\SpotifyWebAPI();
	$api->setAccessToken($spotifySession->getAccessToken());
	$api->setReturnType(\SpotifyWebAPI\SpotifyWebAPI::RETURN_ASSOC);

	try {
		$api->me();
	} catch (SpotifyWebAPIException $e) {

		if ($e->getCode() === 401) {
			unlink(ACCESS_TOKEN_FILE);

			echo 'Refreshing token.';

			$spotifySession = $spotifySession->refresh();
			file_put_contents(ACCESS_TOKEN_FILE, $spotifySession->getAccessToken());
			$api->setAccessToken($spotifySession->getAccessToken());
		}
	}
}

$limit = 50;
$offset = 0;

while (TRUE) {
	$result = $api->getMySavedAlbums([
		'limit' => $limit,
		'offset' => $offset,
	]);

	foreach ($result['items'] as $album) {
		$album = $album['album'];
		$uri = $album['uri'];
		$imageUrl = $album['images'][1]['url'];
		$title = $album['artists'][0]['name'] . ' - ' . substr($album['release_date'], 0, 4) . ' - ' . $album['name'];
		echo '<a href="' . $uri . '"><img src="' . $imageUrl . '" title="' . $title . '" style="margin:10px;"></a>';

	}

	if (count($result['items']) === 0) {
		break;
	}

	$offset += $limit;
}


