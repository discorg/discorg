<?php

declare(strict_types=1);

namespace App\Infrastructure\User;

use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;
use function serialize;
use function unserialize;

class UserSessionManager
{
    private const COOKIE_NAME = 'userSession';

    public function initialize(ServerRequestInterface $request) : UserSession
    {
        $cookies = $request->getCookieParams();

        if (! array_key_exists(self::COOKIE_NAME, $cookies)) {
            return new UserSession();
        }

        $unserializedSession = unserialize($cookies['userSession'], [
            UserSession::class,
        ]);

        if (! $unserializedSession instanceof UserSession) {
            return new UserSession();
        }

        return $unserializedSession;
    }

    public function saveSession(ResponseInterface $response, UserSession $userSession) : ResponseInterface
    {
        $response = $response->withoutHeader('Set-Cookie');

        $cookie = SetCookie::thatStaysForever(self::COOKIE_NAME, serialize($userSession));

        return $cookie->addToResponse($response);
    }
}
