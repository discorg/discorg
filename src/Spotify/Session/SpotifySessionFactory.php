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

	/**
	 * @var string[]
	 */
	private $authorizationScopes = [];

	/**
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $redirectUri
	 * @param string[] $authorizationScopes
	 */
	public function __construct(string $clientId, string $clientSecret, string $redirectUri, array $authorizationScopes)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->redirectUri = $redirectUri;
		$this->authorizationScopes = $authorizationScopes;
	}

	public function createAuthorizable(): AuthorizableSpotifySession
	{
		return new SpotifySessionAdapter(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri,
			$this->authorizationScopes
		);
	}

	public function createAuthorized(string $accessToken, string $refreshToken): AuthorizedSpotifySession
	{
		$session = new SpotifySessionAdapter(
			$this->clientId,
			$this->clientSecret,
			$this->redirectUri,
			$this->authorizationScopes
		);

		return $session->withTokens($accessToken, $refreshToken);
	}

}
