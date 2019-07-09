<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify;

use SpotifyWebAPI\SpotifyWebAPI;
use function array_merge;
use function count;
use function min;

final class SpotifyUserLibraryFacade
{
    private const MAXIMUM_BATCH_SIZE = 50;

    /** @var SpotifyWebAPI */
    private $api;

    public function __construct(SpotifyWebAPI $api)
    {
        $this->api = $api;
    }

    /**
     * @return mixed[]
     */
    public function getAlbums(string $accessToken, int $limit) : array
    {
        $this->api->setAccessToken($accessToken);

        $batchSize = min(self::MAXIMUM_BATCH_SIZE, $limit);
        $offset = 0;
        $albums = [];

        while (true) {
            /** @var mixed[] $result */
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
