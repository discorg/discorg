<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions\Albums;

use App\Infrastructure\Spotify\Session\AuthorizedSpotifySession;
use App\Infrastructure\Spotify\SpotifyUserLibraryFacade;
use Assert\Assertion;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function assert;
use function sprintf;
use function substr;

final class GetAlbums implements RequestHandlerInterface
{
    private SpotifyUserLibraryFacade $spotifyUserLibrary;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(SpotifyUserLibraryFacade $spotifyUserLibrary, ResponseFactoryInterface $responseFactory)
    {
        $this->spotifyUserLibrary = $spotifyUserLibrary;
        $this->responseFactory = $responseFactory;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $spotifySession = $request->getAttribute(AuthorizedSpotifySession::class);
        assert($spotifySession instanceof AuthorizedSpotifySession);
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

        return $response = $this->responseFactory
            ->createResponse(200)
            ->withBody($responseBodyAsStream);
    }
}
