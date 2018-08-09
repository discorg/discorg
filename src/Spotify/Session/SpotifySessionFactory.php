<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

class SpotifySessionFactory
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

	public function createAuthorizable(): AuthorizableSpotifySession
	{
		return new SpotifySessionAdapter(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri
		);
	}

	public function createAuthorized(string $accessToken, string $refreshToken): AuthorizedSpotifySession
	{
		$session = new SpotifySessionAdapter(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri
		);

		return $session->withTokens($accessToken, $refreshToken);
	}

}
