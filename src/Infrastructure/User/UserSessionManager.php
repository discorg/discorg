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

    /**
     * @return mixed[]
     */
    public function initialize(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : array {
        $cookies = $request->getCookieParams();

        if (array_key_exists(self::COOKIE_NAME, $cookies)) {
            $unserializedSession = unserialize($cookies['userSession'], [
                UserSession::class,
            ]);

            if (! $unserializedSession instanceof UserSession) {
                $session = new UserSession();
                $response = $this->saveSession($response, $session);
            } else {
                $session = $unserializedSession;
            }
        } else {
            $session = new UserSession();
            $response = $this->saveSession($response, $session);
        }

        return [$response, $session];
    }

    public function saveSession(ResponseInterface $response, UserSession $userSession) : ResponseInterface
    {
        $response = $response->withoutHeader('Set-Cookie');

        $cookie = SetCookie::thatStaysForever(self::COOKIE_NAME, serialize($userSession));

        return $cookie->addToResponse($response);
    }
}
