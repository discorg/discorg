<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Actions;

use Bouda\SpotifyAlbumTagger\Application\Action;
use Bouda\SpotifyAlbumTagger\Spotify\SpotifyUserLibraryFacade;
use function sprintf;
use function substr;

class AlbumsAction implements Action
{
    /** @var SpotifyUserLibraryFacade */
    private $spotifyUserLibrary;

    public function __construct(SpotifyUserLibraryFacade $spotifyUserLibrary)
    {
        $this->spotifyUserLibrary = $spotifyUserLibrary;
    }

    public function __invoke() : void
    {
        foreach ($this->spotifyUserLibrary->getAlbums(5) as $album) {
            $album = $album['album'];
            $uri = $album['uri'];
            $imageUrl = $album['images'][1]['url'];
            $title = $album['artists'][0]['name']
                . ' - '
                . substr($album['release_date'], 0, 4)
                . ' - ' . $album['name'];
            echo sprintf('<a href="%s">%s</a><br>', $uri, $title);
        }
    }
}
