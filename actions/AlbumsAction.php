<?php

declare(strict_types=1);

namespace App\Actions;

use App\Application\Action;
use App\Spotify\SpotifyUserLibraryFacade;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $responseBody = '';

        foreach ($this->spotifyUserLibrary->getAlbums(5) as $album) {
            $album = $album['album'];
            $uri = $album['uri'];
            $imageUrl = $album['images'][1]['url'];
            $title = $album['artists'][0]['name']
                . ' - '
                . substr($album['release_date'], 0, 4)
                . ' - ' . $album['name'];
            $responseBody .= sprintf('<a href="%s">%s</a><br>', $uri, $title);
        }

        $responseBodyAsStream = (new Psr17Factory())->createStream($responseBody);

        return $response->withStatus(200)->withBody($responseBodyAsStream);
    }
}
