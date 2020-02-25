<?php

declare(strict_types=1);

namespace App\Infrastructure\User;

use HansOtt\PSR7Cookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;
use function serialize;
use function unserialize;

final class UserSessionManager
{
    private const COOKIE_NAME = 'userSession';

    public function initialize(ServerRequestInterface $request) : UserSession
    {
        /** @var string[] $cookies */
        $cookies = $request->getCookieParams();

        if (! array_key_exists(self::COOKIE_NAME, $cookies)) {
            return new UserSession();
        }

        return unserialize($cookies['userSession'], [
            'allowed_classes' => [
                UserSession::class,
            ],
        ]);
    }

    public function saveSession(ResponseInterface $response, UserSession $userSession) : ResponseInterface
    {
        $response = $response->withoutHeader('Set-Cookie');

        $cookie = SetCookie::thatStaysForever(self::COOKIE_NAME, serialize($userSession));

        return $cookie->addToResponse($response);
    }
}
