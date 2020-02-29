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

        self::assertSame(400, $response->getStatusCode());
    }

    public function testInvalidJson1() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/sessions'),
            ['content-type' => 'application/json'],
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testInvalidJson2() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/sessions'),
            ['content-type' => 'application/json'],
            '{',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testInvalidJson3() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/sessions'),
            ['content-type' => 'application/json'],
            '{}',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testValidJson() : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/sessions'),
            ['content-type' => 'application/json'],
            '{"email":"elias@bouda.life","password":"tucek"}',
        );
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->container = (new ServiceContainer());
    }
}
