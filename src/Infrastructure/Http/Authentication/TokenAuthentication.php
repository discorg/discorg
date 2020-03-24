<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use function strlen;
use function strpos;
use function substr;

final class TokenAuthentication
{
    private const HEADER_NAME = 'Authorization';
    private const PREFIX = 'Bearer ';

    private string $token;

    private function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @throws CannotParseAuthentication
     */
    public static function fromRequestHeader(ServerRequestInterface $request) : self
    {
        $rawHeader = $request->getHeaderLine(self::HEADER_NAME);

        if (strpos($rawHeader, self::PREFIX) !== 0) {
            throw CannotParseAuthentication::headerNotFound();
        }

        $token = substr($rawHeader, strlen(self::PREFIX));

        if ($token === '') {
            throw CannotParseAuthentication::invalidCredentialsString();
        }

        return new self($token);
    }

    public function token() : string
    {
        return $this->token;
    }
}
