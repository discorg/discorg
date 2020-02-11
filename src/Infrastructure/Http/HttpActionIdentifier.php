<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Assert\Assertion;
use Psr\Http\Message\RequestInterface;
use function sprintf;

final class HttpActionIdentifier
{
    private string $uriPath;

    private string $httpMethod;

    private function __construct(string $uriPath, string $httpMethod)
    {
        Assertion::startsWith($uriPath, '/');
        Assertion::choice($httpMethod, [
            'GET',
            'POST',
            'PATCH',
            'PUT',
            'DELETE',
        ]);

        $this->uriPath = $uriPath;
        $this->httpMethod = $httpMethod;
    }

    public static function fromRequest(RequestInterface $request) : self
    {
        return new self($request->getUri()->getPath(), $request->getMethod());
    }

    public function toString() : string
    {
        return sprintf('%s %s', $this->httpMethod, $this->uriPath);
    }
}
