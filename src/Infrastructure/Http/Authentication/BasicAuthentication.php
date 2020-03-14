<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use function base64_decode;
use function count;
use function explode;
use function strpos;
use function substr;

final class BasicAuthentication
{
    private string $username;
    private string $password;

    private function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @throws CannotParseAuthentication
     */
    public static function fromRequestHeader(ServerRequestInterface $request) : self
    {
        $rawHeader = $request->getHeaderLine('Authorization');

        if (strpos($rawHeader, 'Basic') !== 0) {
            throw CannotParseAuthentication::headerNotFound();
        }

        $decodedCredentials = base64_decode(substr($rawHeader, 6), true);
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

        return new self($explodedCredentials[0], $explodedCredentials[1]);
    }

    public function username() : string
    {
        return $this->username;
    }

    public function password() : string
    {
        return $this->password;
    }
}
