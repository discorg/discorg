<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

use Bouda\SpotifyAlbumTagger\User\InitializedUserSessionManager;
use SpotifyWebAPI\SpotifyWebAPIException;



class SpotifySessionManager implements InitializableSpotifySessionManager, InitializedSpotifySessionManager
{

	/**
	 * @var InitializedUserSessionManager
	 */
	private $userSessionManager;

	/**
	 * @var SpotifySessionFactory
	 */
	private $spotifySessionFactory;

	/**
	 * @var AuthorizedSpotifySession
	 */
	private $spotifySession;

	public function __construct(InitializedUserSessionManager $userSessionManager, SpotifySessionFactory $spotifySessionFactory)
	{
		$this->userSessionManager = $userSessionManager;
		$this->spotifySessionFactory = $spotifySessionFactory;
	}

	public function initialize(): InitializedSpotifySessionManager
	{
		$userSession = $this->userSessionManager->getSession();

		if (isset($_GET['code'])) {
			$spotifySession = $this->spotifySessionFactory->createAuthorizable();

			$code = $_GET['code'];
			$spotifySession = $spotifySession->authorize($code);


			$userSession->setupSpotify($spotifySession->getAccessToken(), $spotifySession->getRefreshToken());
			$this->userSessionManager->saveSession();

			echo 'Authorizing spotify session with code.';
			header('refresh:1;index.php');
			die;
		}

		if ($userSession->getSpotifyAccessToken() === null) {
			$spotifySession = $this->spotifySessionFactory->createAuthorizable();

			$url = $spotifySession->getAuthorizeUrl();

			echo 'Redirecting to spotify.';
			header('refresh:1;' . $url);
			die;
		}

		$spotifySession = $this->spotifySessionFactory->createAuthorized(
			$userSession->getSpotifyAccessToken(),
			$userSession->getSpotifyRefreshToken()
		);

		$this->spotifySession = $spotifySession;

		$this->refreshTokenIfNeeded();

		return $this;
	}

	public function getSession(): AuthorizedSpotifySession
	{
		return $this->spotifySession;
	}

	private function refreshTokenIfNeeded(): void
	{
		$spotifySession = $this->spotifySession;

		try {
			$api = new \SpotifyWebAPI\SpotifyWebAPI();
			$api->setAccessToken($spotifySession->getAccessToken());
			$api->me();
		} catch (SpotifyWebAPIException $e) {

			if ($e->getCode() === 401) {
				$spotifySession->refresh();

				$userSession = $this->userSessionManager->getSession();
				$userSession->setupSpotify($spotifySession->getAccessToken(), $spotifySession->getRefreshToken());
				$this->userSessionManager->saveSession();
			}
		}
	}

}
