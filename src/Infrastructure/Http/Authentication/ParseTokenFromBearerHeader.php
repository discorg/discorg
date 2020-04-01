<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use function strlen;
use function strpos;
use function substr;

final class ParseTokenFromBearerHeader
{
    private const HEADER_NAME = 'Authorization';
    private const PREFIX = 'Bearer ';

    /**
     * @throws CannotParseAuthentication
     */
    public function __invoke(ServerRequestInterface $request) : string
    {
        $rawHeader = $request->getHeaderLine(self::HEADER_NAME);

        if (strpos($rawHeader, self::PREFIX) !== 0) {
            throw CannotParseAuthentication::headerNotFound();
        }

        $token = substr($rawHeader, strlen(self::PREFIX));

        if ($token === '') {
            throw CannotParseAuthentication::invalidCredentialsString();
        }

        return $token;
    }
}
