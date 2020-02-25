<?php

declare(strict_types=1);

namespace Tests\EndToEnd\Api;

use App\Infrastructure\ServiceContainer;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;

final class HealthCheckTest extends TestCase
{
    private ServiceContainer $container;

    public function testHealthCheckEndpointDoesNotExistYet() : void
    {
        $request = new ServerRequest('GET', new Uri('http://discorg.bouda.life/api/v1'));
        $response = $this->container->httpApplication()->handle($request);

        self::assertSame(404, $response->getStatusCode());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->container = (new ServiceContainer());
    }
}
