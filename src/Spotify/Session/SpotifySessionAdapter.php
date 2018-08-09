<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

use Assert\Assertion;

class SpotifySessionAdapter implements AuthorizableSpotifySession, AuthorizedSpotifySession
{

	/**
	 * @var \SpotifyWebAPI\Session
	 */
	private $session;

	/**
	 * @var string
	 */
	private $accessToken;

	public function __construct(string $clientId, string $clientSecret, string $redirectUri)
	{
		Assertion::notEmpty($clientId);
		Assertion::notEmpty($clientSecret);
		Assertion::notEmpty($redirectUri);

		$this->session = new \SpotifyWebAPI\Session(
			$clientId,
			$clientSecret,
			$redirectUri
		);
	}

	public function getAuthorizeUrl(): string
	{
		$options = [
			'scope' => [
				'user-library-read',
				'user-read-recently-played',
				'user-read-currently-playing',
			],
		];

		return $this->session->getAuthorizeUrl($options);
	}

	public function authorize(string $authorizationCode): AuthorizedSpotifySession
	{
		$result = $this->session->requestAccessToken($authorizationCode);

		if ($result === false) {
			throw new \RuntimeException('Access token not granted.');
		}

		$this->accessToken = $this->session->getAccessToken();

		return $this;
	}

	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	public function getRefreshToken(): string
	{
		return $this->session->getRefreshToken();
	}

	public function withTokens(string $accessToken, string $refreshToken): AuthorizedSpotifySession
	{
		$this->accessToken = $accessToken;
		$this->session->setRefreshToken($refreshToken);

		return $this;
	}

	public function refresh(): AuthorizedSpotifySession
	{
		$this->session->refreshAccessToken($this->session->getRefreshToken());
		$this->accessToken = $this->session->getAccessToken();

		return $this;
	}

}
