<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

interface AuthorizableSpotifySession
{

	public function getAuthorizeUrl(): string;

	public function authorize(string $authorizationCode): AuthorizedSpotifySession;

}
