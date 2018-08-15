<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\User;

interface InitializableUserSessionManager
{

	public function initialize(): InitializedUserSessionManager;

}
