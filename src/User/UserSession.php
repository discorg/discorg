<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\User;

class UserSession
{

	/**
	 * @var string
	 */
	private $spotifyAccessToken;

	/**
	 * @var string
	 */
	private $spotifyRefreshToken;

	public function setupSpotify(string $accessToken, string $refreshToken): void
	{
		$this->spotifyAccessToken = $accessToken;
		$this->spotifyRefreshToken = $refreshToken;
	}

	public function getSpotifyAccessToken(): ?string
	{
		return $this->spotifyAccessToken;
	}



	public function getSpotifyRefreshToken(): ?string
	{
		return $this->spotifyRefreshToken;
	}

}
