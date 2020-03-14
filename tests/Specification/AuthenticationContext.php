<?php

declare(strict_types=1);

namespace Tests\Specification;

use App\Infrastructure\ServiceContainer;
use Behat\Behat\Context\Context;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use function base64_encode;
use function json_decode;
use function json_encode;
use function sprintf;
use const JSON_THROW_ON_ERROR;

final class AuthenticationContext implements Context
{
    private static ServiceContainer $container;
    /** @var string[] */
    private array $tokensByEmail = [];

    /** @BeforeScenario */
    public static function setup() : void
    {
        self::$container = new ServiceContainer();
    }

    /**
     * @Given /^there are no registered users$/
     */
    public function thereAreNoRegisteredUsers() : void
    {
    }

    /**
     * @When /^user registers with email address "([^"]*)" and password "([^"]*)"$/
     */
    public function userRegistersWithUsernameAndPassword(string $emailAddress, string $password) : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
            json_encode([
                'email' => $emailAddress,
                'password' => $password,
            ], JSON_THROW_ON_ERROR),
        );
        $response = self::$container->httpApplication()->handle($request);

        Assert::assertSame(200, $response->getStatusCode(), $response->getReasonPhrase());
    }

    /**
     * @Then /^another registration with email address "([^"]*)" fails$/
     */
    public function anotherRegistrationWithEmailAddressFails(string $emailAddress) : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user'),
            ['content-type' => 'application/json'],
            json_encode([
                'email' => $emailAddress,
                'password' => 'secret456',
            ], JSON_THROW_ON_ERROR),
        );
        $response = self::$container->httpApplication()->handle($request);

        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame(
            'Email address "ondrej@bouda.life" has already been registered.',
            $response->getReasonPhrase(),
        );
    }

    /**
     * @Given /^user starts a session with email address "([^"]*)" and password "([^"]*)"$/
     */
    public function userStartsASessionWithUsernameAndPassword(string $emailAddress, string $password) : void
    {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user/me/session'),
            [
                'content-type' => 'application/json',
                'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:%s', $emailAddress, $password))),
            ],
        );
        $response = self::$container->httpApplication()->handle($request);

        Assert::assertSame(200, $response->getStatusCode(), $response->getReasonPhrase());

        $this->tokensByEmail[$emailAddress] = json_decode((string) $response->getBody(), true)['token'];
    }

    /**
     * @Then /^user session is started for user "([^"]*)"$/
     */
    public function userSessionIsStarted(string $emailAddress) : void
    {
        $request = new ServerRequest(
            'GET',
            new Uri('http://discorg.bouda.life/api/v1/user/me/session'),
            [
                'content-type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->tokensByEmail[$emailAddress]),
            ],
        );
        $response = self::$container->httpApplication()->handle($request);

        Assert::assertSame(200, $response->getStatusCode(), $response->getReasonPhrase());
    }

    /**
     * @Given /^there is a previously registered user that registered with username "([^"]*)" and password "([^"]*)"$/
     */
    public function thereIsAPreviouslyRegisteredUserThatRegisteredWithUsernameAndPassword(
        string $emailAddress,
        string $password
    ) : void {
        $this->userRegistersWithUsernameAndPassword($emailAddress, $password);
    }

    /**
     * @Then /^there are two different sessions started for user "([^"]*)"$/
     */
    public function thereAreTwoDifferentSessionsStartedForUser(string $emailAddress) : void
    {
        $request = new ServerRequest(
            'GET',
            new Uri('http://discorg.bouda.life/api/v1/user/me/session'),
            [
                'content-type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->tokensByEmail[$emailAddress]),
            ],
        );
        $response = self::$container->httpApplication()->handle($request);

        Assert::assertSame(200, $response->getStatusCode(), $response->getReasonPhrase());

        $sessionCollection = json_decode((string) $response->getBody(), true);

        Assert::assertCount(2, $sessionCollection);
        Assert::assertNotSame($sessionCollection[0]['token'], $sessionCollection[1]['token']);
    }

    /**
     * @Then /^starting a session with email address "([^"]*)" and password "([^"]*)" fails$/
     */
    public function startingASessionWithEmailAddressAndPasswordFails(
        string $emailAddress,
        string $password
    ) : void {
        $request = new ServerRequest(
            'POST',
            new Uri('http://discorg.bouda.life/api/v1/user/me/session'),
            [
                'content-type' => 'application/json',
                'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:%s', $emailAddress, $password))),
            ],
        );
        $response = self::$container->httpApplication()->handle($request);

        Assert::assertSame(401, $response->getStatusCode(), $response->getReasonPhrase());
        Assert::assertSame('Unauthorized', $response->getReasonPhrase());
    }
}
