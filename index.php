<?php declare(strict_types = 1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/spotifyClientConfig.php';

use SpotifyWebAPI\SpotifyWebAPIException;
use Tracy\Debugger;

Debugger::enable();
Debugger::$maxDepth = 7;

const WWW_URL = 'http://localhost:8000';
const CACHE_DIR = __DIR__ . '/var/cache';

const ACCESS_TOKEN_FILE = CACHE_DIR . '/access-token';
const REFRESH_TOKEN_FILE = CACHE_DIR . '/refresh-token';


if (!file_exists(ACCESS_TOKEN_FILE) && !isset($_GET['code'])) {
	$session = new SpotifyWebAPI\Session(
		SPOTIFY_CLIENT_ID,
		SPOTIFY_CLIENT_SECRET,
		WWW_URL
	);

	$options = [
		'scope' => [
			'user-library-read',
			'user-read-recently-played',
			'user-read-currently-playing',
		],
	];

	echo 'Redirecting to spotify.';

	header('refresh:1;' . $session->getAuthorizeUrl($options));
	die();

} elseif (isset($_GET['code'])) {
	$session = new SpotifyWebAPI\Session(
		SPOTIFY_CLIENT_ID,
		SPOTIFY_CLIENT_SECRET,
		WWW_URL
	);

	$session->requestAccessToken($_GET['code']);

	file_put_contents(ACCESS_TOKEN_FILE, $session->getAccessToken());
	file_put_contents(REFRESH_TOKEN_FILE, $session->getRefreshToken());

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

			$session = new SpotifyWebAPI\Session(
				SPOTIFY_CLIENT_ID,
				SPOTIFY_CLIENT_SECRET,
				WWW_URL
			);

			$session->refreshAccessToken($refreshToken);
			$accessToken = $session->getAccessToken();
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


