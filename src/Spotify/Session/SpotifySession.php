<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

class SpotifySession
{

	/**
	 * @var string
	 */
	private $clientId;

	/**
	 * @var string
	 */
	private $clientSecret;

	/**
	 * @var string
	 */
	private $redirectUri;

	public function __construct(string $clientId, string $clientSecret, string $redirectUri)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->redirectUri = $redirectUri;
	}

	public function getAuthorizeUrl(): string
	{
		$session = new \SpotifyWebAPI\Session(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri
		);

		$options = [
			'scope' => [
				'user-library-read',
				'user-read-recently-played',
				'user-read-currently-playing',
			],
		];

		return $session->getAuthorizeUrl($options);
	}

	/**
	 * @param string $authorizationCode
	 * @return string[]
	 */
	public function requestTokens(string $authorizationCode): array
	{
		$session = new \SpotifyWebAPI\Session(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri
		);

		$result = $session->requestAccessToken($authorizationCode);

		if ($result === false) {
			throw new \Exception('Access token not granted.');
		}

		return [
			$session->getAccessToken(),
			$session->getRefreshToken(),
		];
	}

	public function refreshAccessToken(string $refreshToken): string
	{
		$session = new \SpotifyWebAPI\Session(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri
		);

		$session->refreshAccessToken($refreshToken);

		return $session->getAccessToken();
	}

}
