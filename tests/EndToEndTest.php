<?php

declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Application\ServiceContainer;
use App\Infrastructure\User\UserSession;
use HansOtt\PSR7Cookies\SetCookie;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use VCR\VCR;
use function serialize;

class EndToEndTest extends TestCase
{
    /** @var ServiceContainer */
    private $container;

    public function testUninitialized() : void
    {
        $application = $this->container->httpApplication();

        $request = new ServerRequest('GET', new Uri('http://fake'));
        $response = $application->processRequest($request);

        Assert::assertSame(200, $response->getStatusCode());

        $userSessionHeader = $response->getHeaderLine('Set-Cookie');
        Assert::assertSame(
            SetCookie::thatStaysForever('userSession', serialize(new UserSession()))->toHeaderValue(),
            $userSessionHeader,
        );

        $spotifyHeader = $response->getHeaderLine('Refresh');
        Assert::assertSame(
            '1;'
            . 'https://accounts.spotify.com/authorize/'
            . '?client_id=10000000001000000000100000000010'
            . '&redirect_uri=http%3A%2F%2Ffake'
            . '&response_type=code'
            . '&scope=user-library-read+user-library-modify+playlist-read-private+playlist-modify-public'
            . '+playlist-modify-private+playlist-read-collaborative+user-read-recently-played+user-top-read'
            . '+user-read-private+user-read-email+user-read-birthdate+user-modify-playback-state'
            . '+user-read-currently-playing+user-read-playback-state+user-follow-modify+user-follow-read',
            $spotifyHeader,
        );

        Assert::assertSame('Redirecting to spotify.', (string) $response->getBody());
    }

    public function testSpotifyAuthentication() : void
    {
        VCR::configure()->setCassettePath(__DIR__ . '/fixtures');
        VCR::insertCassette('spotifyAuthenticationRequest.yml');

        $application = $this->container->httpApplication();

        $request = (new ServerRequest('GET', new Uri('http://fake?code=some-code')))
            ->withQueryParams(['code' => 'koMDcP0ddBuWQlI1bFBWbbNc3j--NFs']);

        $response = $application->processRequest($request);

        Assert::assertSame(200, $response->getStatusCode());

        $expectedUserSession = new UserSession();
        $expectedUserSession->setupSpotify(
            'BQAR7OMMTw4M1ZZqsmU6J_5ZNvBUfvjKeoe6P6Vf0a3SdZ-0XHoOMRBTn',
            'AQCRYYgWRUcbSxnIuBSpbDiqy0S1Myc',
        );
        $userSessionHeader = $response->getHeaderLine('Set-Cookie');
        Assert::assertSame(
            SetCookie::thatStaysForever('userSession', serialize($expectedUserSession))->toHeaderValue(),
            $userSessionHeader,
        );

        Assert::assertSame('Authorizing spotify session with code.', (string) $response->getBody());
    }

    public function testHelloWorld() : void
    {
        VCR::configure()->setCassettePath(__DIR__ . '/fixtures');
        VCR::insertCassette('spotifyMeRequest.yml');

        $application = $this->container->httpApplication();

        $userSession = new UserSession();
        $userSession->setupSpotify(
            'BQAR7OMMTw4M1ZZqsmU6J_5ZNvBUfvjKeoe6P6Vf0a3SdZ-0XHoOMRBTn',
            'AQCRYYgWRUcbSxnIuBSpbDiqy0S1Myc',
        );

        $request = (new ServerRequest('GET', new Uri('http://fake')))
            ->withCookieParams([
                'userSession' => serialize($userSession),
            ]);

        $response = $application->processRequest($request);

        Assert::assertSame(200, $response->getStatusCode());

        Assert::assertSame('Hello world', (string) $response->getBody());
    }

    public function testExpiredSpotifyAccessToken() : void
    {
        VCR::configure()->setCassettePath(__DIR__ . '/fixtures');
        VCR::insertCassette('spotifyExpiredTokenRequests.yml');

        $application = $this->container->httpApplication();

        $userSession = new UserSession();
        $userSession->setupSpotify(
            'BQAR7OMMTw4M1ZZqsmU6J_5ZNvBUfvjKeoe6P6Vf0a3SdZ-0XHoOMRBTn',
            'AQCRYYgWRUcbSxnIuBSpbDiqy0S1Myc',
        );

        $request = (new ServerRequest('GET', new Uri('http://fake')))
            ->withCookieParams([
                'userSession' => serialize($userSession),
            ]);

        $response = $application->processRequest($request);

        Assert::assertSame(200, $response->getStatusCode());

        Assert::assertSame('Hello world', (string) $response->getBody());
    }

    public function testAlbums() : void
    {
        VCR::configure()->setCassettePath(__DIR__ . '/fixtures');
        VCR::insertCassette('spotifyAlbumsRequests.yml');

        $application = $this->container->httpApplication();

        $userSession = new UserSession();
        $userSession->setupSpotify(
            'BQAR7OMMTw4M1ZZqsmU6J_5ZNvBUfvjKeoe6P6Vf0a3SdZ-0XHoOMRBTn',
            'AQCRYYgWRUcbSxnIuBSpbDiqy0S1Myc',
        );

        $request = (new ServerRequest('GET', new Uri('http://fake?action=albums')))
            ->withQueryParams(['action' => 'albums'])
            ->withCookieParams([
                'userSession' => serialize($userSession),
            ]);

        $response = $application->processRequest($request);

        Assert::assertSame(200, $response->getStatusCode());

        Assert::assertStringEqualsFile(
            __DIR__ . '/fixtures/expectedAlbumsResponseBody.html',
            (string) $response->getBody()
        );
    }

    protected function setUp() : void
    {
        parent::setUp();

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/fixtures/.env');

        $this->container = (new ServiceContainer());

        VCR::turnOn();
        // no http requests should be made
        VCR::configure()->setMode('none');
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        VCR::turnOff();
    }
}
