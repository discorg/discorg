<?php

declare(strict_types=1);

namespace Tests\EndToEnd\Api;

use App\Infrastructure\ServiceContainer;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;

final class CommonApiTest extends TestCase
{
    private ServiceContainer $container;

    public function testNonexistentEndpoint() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/nonexistent'),
            ['content-type' => 'application/json'],
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(404, $response->getStatusCode(), $response->getReasonPhrase());
    }

    public function testMissingAuthentication() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode(), $response->getReasonPhrase());
    }

    public function testInvalidJson1() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode(), $response->getReasonPhrase());
    }

    public function testInvalidJson2() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
            '{',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode(), $response->getReasonPhrase());
    }

    public function testInvalidJson3() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
            '{}',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode(), $response->getReasonPhrase());
    }

    public function testValidJson() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
            '{"email":"elias@bouda.life","password":"secret123"}',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(200, $response->getStatusCode(), $response->getReasonPhrase());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->container = (new ServiceContainer());
    }
}
