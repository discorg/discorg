<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Spotify/Session/SpotifySession.php';

use SpotifyWebAPI\SpotifyWebAPIException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

$containerBuilder = new ContainerBuilder();
$loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
$loader->load('src/Spotify/config.yaml');
$loader->load('config/config.local.yaml');

$spotifySession = $containerBuilder->get('spotifySession');


const CACHE_DIR = __DIR__ . '/var/cache';

const ACCESS_TOKEN_FILE = CACHE_DIR . '/access-token';
const REFRESH_TOKEN_FILE = CACHE_DIR . '/refresh-token';


if (!file_exists(ACCESS_TOKEN_FILE) && !isset($_GET['code'])) {
	$url = $spotifySession->getAuthorizeUrl();

	echo 'Redirecting to spotify.';

	header('refresh:1;' . $url);
	die();

} elseif (isset($_GET['code'])) {
	$code = $_GET['code'];
	[$accessToken, $refreshToken] = $spotifySession->requestTokens($code);

	file_put_contents(ACCESS_TOKEN_FILE, $accessToken);
	file_put_contents(REFRESH_TOKEN_FILE, $refreshToken);

	header('refresh:1;index.php');
	die();

} elseif (file_exists(ACCESS_TOKEN_FILE)) {
	$token = file_get_contents(ACCESS_TOKEN_FILE);

	$api = new SpotifyWebAPI\SpotifyWebAPI();
	$api->setAccessToken($token);
	$api->setReturnType(\SpotifyWebAPI\SpotifyWebAPI::RETURN_ASSOC);

	try {
		$api->me();
	} catch (SpotifyWebAPIException $e) {

		if ($e->getCode() === 401) {
			unlink(ACCESS_TOKEN_FILE);
			$refreshToken = file_get_contents(REFRESH_TOKEN_FILE);

			echo 'Refreshing token.';

			$accessToken = $spotifySession->refreshAccessToken($refreshToken);
			file_put_contents(ACCESS_TOKEN_FILE, $accessToken);
			$api->setAccessToken($accessToken);
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


