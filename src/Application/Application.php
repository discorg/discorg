<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Application;

use Bouda\SpotifyAlbumTagger\Spotify\Session\InitializableSpotifySessionManager;
use Bouda\SpotifyAlbumTagger\User\InitializableUserSessionManager;
use Psr\Container\ContainerInterface;

class Application
{

	/**
	 * @var InitializableUserSessionManager
	 */
	private $userSessionManager;

	/**
	 * @var InitializableSpotifySessionManager
	 */
	private $spotifySessionManager;

	/**
	 * @var ActionResolver
	 */
	private $actionResolver;

	public function __construct(
		InitializableUserSessionManager $userSessionManager,
		InitializableSpotifySessionManager $spotifySessionManager,
		ActionResolver $actionResolver
	)
	{
		$this->userSessionManager = $userSessionManager;
		$this->spotifySessionManager = $spotifySessionManager;
		$this->actionResolver = $actionResolver;
	}

	public function run(ContainerInterface $container): void
	{
		$this->userSessionManager->initialize();
		$this->spotifySessionManager->initialize();

		$action = $this->actionResolver->resolve($container);

		$action();
	}

}
