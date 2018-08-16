<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Application;

interface Action
{

	public function __invoke(): void;

}
