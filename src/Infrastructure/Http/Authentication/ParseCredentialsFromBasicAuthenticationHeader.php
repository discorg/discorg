<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use App\Domain\UserAuthentication\UserCredentials;
use Psr\Http\Message\ServerRequestInterface;
use function base64_decode;
use function count;
use function explode;
use function strlen;
use function strpos;
use function substr;

final class ParseCredentialsFromBasicAuthenticationHeader
{
    private const HEADER_NAME = 'Authorization';
    private const PREFIX = 'Basic ';

    /**
     * @throws CannotParseAuthentication
     */
    public function __invoke(ServerRequestInterface $request) : UserCredentials
    {
        $rawHeader = $request->getHeaderLine(self::HEADER_NAME);

        if (strpos($rawHeader, self::PREFIX) !== 0) {
            throw CannotParseAuthentication::headerNotFound();
        }

        $rawCredentials = substr($rawHeader, strlen(self::PREFIX));
        $decodedCredentials = base64_decode($rawCredentials, true);
        if ($decodedCredentials === false) {
            throw CannotParseAuthentication::invalidEncoding();
        }

        $explodedCredentials = explode(':', $decodedCredentials);
        if (count($explodedCredentials) !== 2) {
            throw CannotParseAuthentication::invalidCredentialsString();
        }

        if ($explodedCredentials[0] === '' || $explodedCredentials[1] === '') {
            throw CannotParseAuthentication::invalidCredentialsString();
        }

        return UserCredentials::fromStrings($explodedCredentials[0], $explodedCredentials[1]);
    }
}
