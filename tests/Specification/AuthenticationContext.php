<?php

declare(strict_types=1);

namespace Tests\Specification;

use Behat\Behat\Context\Context;

final class AuthenticationContext implements Context
{
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
    }

    /**
     * @Given /^user starts a session with email address "([^"]*)" and password "([^"]*)"$/
     */
    public function userStartsASessionWithUsernameAndPassword(string $emailAddress, string $password) : void
    {
    }

    /**
     * @Then /^user session is started for user "([^"]*)"$/
     */
    public function userSessionIsStarted(string $emailAddress) : void
    {
    }
}
