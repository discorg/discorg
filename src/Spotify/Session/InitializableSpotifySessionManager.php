<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify\Session;

interface InitializableSpotifySessionManager
{

	public function initialize(): InitializedSpotifySessionManager;

}
