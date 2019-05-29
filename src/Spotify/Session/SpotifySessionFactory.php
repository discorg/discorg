<?php

declare(strict_types=1);

namespace App\Spotify\Session;

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
        return new SpotifySessionAdapter(
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
        $session = new SpotifySessionAdapter(
            $this->clientId,
            $this->clientSecret,
            $redirectUri,
            $this->authorizationScopes
        );

        return $session->withTokens($accessToken, $refreshToken);
    }
}
