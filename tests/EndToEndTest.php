<?php

declare(strict_types=1);

namespace Tests;

use Bouda\SpotifyAlbumTagger\Application\ContainerFactory;
use Bouda\SpotifyAlbumTagger\Application\HttpApplication;
use Bouda\SpotifyAlbumTagger\User\UserSession;
use HansOtt\PSR7Cookies\SetCookie;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Dotenv\Dotenv;
use VCR\VCR;
use function serialize;

class EndToEndTest extends TestCase
{
    /** @var Container */
    private $container;

    public function testUninitialized() : void
    {
        $application = $this->container->get(HttpApplication::class);

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

    protected function setUp() : void
    {
        parent::setUp();

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/fixtures/.env');

        $this->container = (new ContainerFactory())->create(__DIR__ . '/../');

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
