<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Session;

use Assert\Assertion;
use SpotifyWebAPI\Session;

final class AuthorizedSpotifySession
{
    private Session $session;

    private string $accessToken;

    private function __construct(Session $session, string $accessToken)
    {
        $this->session = $session;
        $this->accessToken = $accessToken;
    }

    public static function fromValues(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $accessToken,
        string $refreshToken
    ) : self {
        Assertion::notEmpty($clientId);
        Assertion::notEmpty($clientSecret);
        Assertion::notEmpty($redirectUri);

        $session = new Session(
            $clientId,
            $clientSecret,
            $redirectUri
        );

        $session->setRefreshToken($refreshToken);

        return new self(
            $session,
            $accessToken
        );
    }

    public static function fromAuthorizable(Session $session, string $accessToken) : self
    {
        return new self($session, $accessToken);
    }

    public function getAccessToken() : string
    {
        return $this->accessToken;
    }

    public function getRefreshToken() : string
    {
        return $this->session->getRefreshToken();
    }

    public function refresh() : AuthorizedSpotifySession
    {
        $this->session->refreshAccessToken($this->session->getRefreshToken());
        $this->accessToken = $this->session->getAccessToken();

        return $this;
    }
}
