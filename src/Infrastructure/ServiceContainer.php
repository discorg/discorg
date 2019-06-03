<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\Actions\AlbumsAction;
use App\Infrastructure\Actions\HomeAction;
use App\Infrastructure\Application\Action;
use App\Infrastructure\Application\ActionResolver;
use App\Infrastructure\Application\HttpApplication;
use App\Infrastructure\Spotify\Session\SpotifySessionFactory;
use App\Infrastructure\Spotify\SpotifyUserLibraryFacade;
use App\Infrastructure\User\UserSessionManager;
use RuntimeException;
use SpotifyWebAPI\SpotifyWebAPI;
use function getenv;

final class ServiceContainer
{
    public function httpApplication() : HttpApplication
    {
        return new HttpApplication(
            $this->userSessionManager(),
            $this->spotifySessionFactory(),
            $this->actionResolver(),
        );
    }

    private function userSessionManager() : UserSessionManager
    {
        static $userSessionManager;

        return $userSessionManager ?? $userSessionManager = new UserSessionManager();
    }

    private function spotifySessionFactory() : SpotifySessionFactory
    {
        static $spotifySessionFactory;

        return $spotifySessionFactory ?? new SpotifySessionFactory(
            (string) getenv('SPOTIFY_CLIENT_ID'),
            (string) getenv('SPOTIFY_CLIENT_SECRET'),
            [
                // Library
                'user-library-read',
                'user-library-modify',
                // Playlists
                'playlist-read-private',
                'playlist-modify-public',
                'playlist-modify-private',
                'playlist-read-collaborative',
                // Listening History
                'user-read-recently-played',
                'user-top-read',
                // Users
                'user-read-private',
                'user-read-email',
                'user-read-birthdate',
                // Spotify Connect
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-playback-state',
                // Follow
                'user-follow-modify',
                'user-follow-read',
            ]
        );
    }

    private function actionResolver() : ActionResolver
    {
        static $actionResolver;

        return $actionResolver ?? $actionResolver = new ActionResolver($this);
    }

    /**
     * @throws RuntimeException
     */
    public function getAction(string $actionServiceName) : Action
    {
        switch ($actionServiceName) {
            case HomeAction::class:
                return new HomeAction();
            case AlbumsAction::class:
                return $this->albumsAction();
            default:
                throw new RuntimeException('Action not found.');
        }
    }

    private function albumsAction() : AlbumsAction
    {
        return new AlbumsAction(
            $this->spotifyUserLibrary(),
        );
    }

    private function spotifyUserLibrary() : SpotifyUserLibraryFacade
    {
        static $spotifyUserLibrary;

        return $spotifyUserLibrary ?? $spotifyUserLibrary = new SpotifyUserLibraryFacade($this->spotifyWebAPI());
    }

    private function spotifyWebAPI() : SpotifyWebAPI
    {
        $api = new SpotifyWebAPI();
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);

        return $api;
    }
}
