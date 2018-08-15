<?php declare(strict_types = 1);

namespace Bouda\SpotifyAlbumTagger\Spotify;

use SpotifyWebAPI\SpotifyWebAPI;



class SpotifyUserLibraryFacade
{

	private const MAXIMUM_BATCH_SIZE = 50;

	/**
	 * @var SpotifyWebAPI
	 */
	private $api;

	public function __construct(SpotifyWebAPI $api)
	{
		$this->api = $api;
	}

	/**
	 * @param int $limit
	 * @return mixed[]
	 */
	public function getAlbums(int $limit): array
	{
		$batchSize = min(self::MAXIMUM_BATCH_SIZE, $limit);
		$offset = 0;
		$albums = [];

		while (true) {
			$result = $this->api->getMySavedAlbums([
				'limit' => $batchSize,
				'offset' => $offset,
			]);

			$items = $result['items'];

			if (count($items) === 0) {
				break;
			}

			$albums = array_merge($albums, $items);

			if (count($albums) >= $limit) {
				break;
			}

			$offset += self::MAXIMUM_BATCH_SIZE;
		}

		return $albums;
	}

}
