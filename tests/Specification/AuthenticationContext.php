<?php

declare(strict_types=1);

namespace Tests\Specification;

use App\Infrastructure\ServiceContainer;
use Behat\Behat\Context\Context;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use function array_pop;
use function base64_encode;
use function json_decode;
use function json_encode;
use function sprintf;
use function substr;
use const JSON_THROW_ON_ERROR;

final class AuthenticationContext implements Context
{
    private static ServiceContainer $container;
    /** @var string[] */
    private array $tokensByEmail = [];
    /** @var ResponseInterface[]  */
    private array $responses = [];

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
        $this->handleRequest($request);
    }

    /**
     * @When /^user starts a session with email address "([^"]*)" and password "([^"]*)"$/
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
        $response = $this->handleRequest($request);

        if (! $this->isResponseSuccessful($response)) {
            return;
        }

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
        $this->handleRequest($request);
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
        $response = $this->handleRequest($request);

        $sessionCollection = json_decode((string) $response->getBody(), true);

        Assert::assertCount(2, $sessionCollection);
        Assert::assertNotSame($sessionCollection[0]['token'], $sessionCollection[1]['token']);
    }

    /**
     * @Then /^the action fails with invalid email address error "([^"]*)"$/
     */
    public function theActionFailsWithInvalidEmailAddressError(string $emailAddress) : void
    {
        $response = $this->popLastResponse();

        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame(
            sprintf('Value \'%s\' does not match format email of type string', $emailAddress),
            $response->getReasonPhrase(),
        );
    }

    /**
     * @Then /^the action fails with invalid password error "([^"]*)"$/
     */
    public function theActionFailsWithInvalidPasswordError(string $password) : void
    {
        $response = $this->popLastResponse();

        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame(
            sprintf('Keyword validation failed: Length of \'%s\' must be longer or equal to 7', $password),
            $response->getReasonPhrase(),
        );
    }

    /**
     * @Then /^the action fails with already registered error$/
     */
    public function theActionFailsWithAlreadyRegisteredError() : void
    {
        $response = $this->popLastResponse();

        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame(
            'Email address "ondrej@bouda.life" has already been registered.',
            $response->getReasonPhrase(),
        );
    }

    /**
     * @Then /^the action fails as not authorized$/
     */
    public function theActionFailsAsNotAuthorized() : void
    {
        $response = $this->popLastResponse();

        Assert::assertSame(401, $response->getStatusCode(), $response->getReasonPhrase());
        Assert::assertSame('Unauthorized', $response->getReasonPhrase());
    }

    private function handleRequest(ServerRequest $request) : ResponseInterface
    {
        $response = self::$container->httpApplication()->handle($request);
        $this->responses[] = $response;

        return $response;
    }

    private function popLastResponse() : ResponseInterface
    {
        $result = array_pop($this->responses);
        Assert::assertNotNull($result);

        return $result;
    }

    /**
     * @AfterScenario
     */
    public function allRequestsWereSuccessful() : void
    {
        foreach ($this->responses as $response) {
            Assert::assertTrue($this->isResponseSuccessful($response), $response->getReasonPhrase());
        }
    }

    private function isResponseSuccessful(ResponseInterface $response) : bool
    {
        // 2XX status code
        return substr((string) $response->getStatusCode(), 0, 1) === '2';
    }
}
