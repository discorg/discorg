<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Session;

class SpotifySessionFactory
{
    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var string[] */
    private $authorizationScopes = [];

    /**
     * @param string[] $authorizationScopes
     */
    public function __construct(string $clientId, string $clientSecret, array $authorizationScopes)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authorizationScopes = $authorizationScopes;
    }

    public function createAuthorizable(string $redirectUri) : AuthorizableSpotifySession
    {
        return AuthorizableSpotifySession::fromValues(
            $this->clientId,
            $this->clientSecret,
            $redirectUri,
            $this->authorizationScopes
        );
    }

    public function createAuthorized(
        string $redirectUri,
        string $accessToken,
        string $refreshToken
    ) : AuthorizedSpotifySession {
        return AuthorizedSpotifySession::fromValues(
            $this->clientId,
            $this->clientSecret,
            $redirectUri,
            $accessToken,
            $refreshToken
        );
    }
}
