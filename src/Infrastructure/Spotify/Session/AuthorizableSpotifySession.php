<?php

declare(strict_types=1);

namespace App\Infrastructure\Spotify\Session;

use Assert\Assertion;
use SpotifyWebAPI\Session;

final class AuthorizableSpotifySession
{
    /** @var Session */
    private $session;

    /** @var string[] */
    private $authorizationScopes = [];

    /**
     * @param string[] $authorizationScopes
     */
    public function __construct(Session $session, array $authorizationScopes)
    {
        $this->session = $session;
        $this->authorizationScopes = $authorizationScopes;
    }

    /**
     * @param string[] $authorizationScopes
     */
    public static function fromValues(
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        array $authorizationScopes
    ) : self {
        Assertion::notEmpty($clientId);
        Assertion::notEmpty($clientSecret);
        Assertion::notEmpty($redirectUri);

        $session = new Session(
            $clientId,
            $clientSecret,
            $redirectUri
        );

        Assertion::allString($authorizationScopes);

        return new self($session, $authorizationScopes);
    }

    public function getAuthorizeUrl() : string
    {
        $options = [
            'scope' => $this->authorizationScopes,
        ];

        return $this->session->getAuthorizeUrl($options);
    }

    /**
     * @throws AuthorizationFailed
     */
    public function authorize(string $authorizationCode) : AuthorizedSpotifySession
    {
        $result = $this->session->requestAccessToken($authorizationCode);

        if ($result === false) {
            throw AuthorizationFailed::accessTokenNotGranted();
        }

        return AuthorizedSpotifySession::fromAuthorizable(
            $this->session,
            $this->session->getAccessToken()
        );
    }
}
