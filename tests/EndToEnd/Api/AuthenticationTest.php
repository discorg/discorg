<?php

declare(strict_types=1);

namespace Tests\EndToEnd\Api;

use App\Infrastructure\ServiceContainer;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;

final class AuthenticationTest extends TestCase
{
    private ServiceContainer $container;

    public function testLoginWithInvalidJson1() : void
    {
        $request = new ServerRequest('POST', new Uri('http://discorg.bouda.life/api/v1/session'));
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testLoginWithInvalidJson2() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/session'),
            [],
            '{',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testLoginWithValidJsonButInvalidData() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/session'),
            [],
            '{}',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testLoginWithValidJson() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/session'),
            [],
            '{"email":"elias@bouda.life","password":"tucek"}',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('{"token":"12345"}', (string) $response->getBody());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->container = (new ServiceContainer());
    }
}
