<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions;

use App\Infrastructure\Http\Action;
use App\Infrastructure\Spotify\Session\AuthorizedSpotifySession;
use App\Infrastructure\Spotify\SpotifyUserLibraryFacade;
use Assert\Assertion;
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
        /** @var AuthorizedSpotifySession $spotifySession */
        $spotifySession = $request->getAttribute(AuthorizedSpotifySession::class);
        Assertion::isInstanceOf($spotifySession, AuthorizedSpotifySession::class);

        $responseBody = '';

        foreach ($this->spotifyUserLibrary->getAlbums($spotifySession->getAccessToken(), 5) as $album) {
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
