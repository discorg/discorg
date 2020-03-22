Feature: User can register and manage their sessions
  In order to use the application
  as a user
  I must be able to authenticate.

  Scenario: User can register and start a session
    Given there are no registered users
    When user registers with email address "ondrej@bouda.life" and password "secret123"
    And user starts a session with email address "ondrej@bouda.life" and password "secret123"
    Then user session is started for user "ondrej@bouda.life"

  Scenario: User cannot register with invalid email address
    Given there are no registered users
    When user registers with email address "non-email" and password "secret123"
    Then the action fails with invalid email address error "non-email"

  Scenario: User cannot register with short password
    Given there are no registered users
    When user registers with email address "ondrej@bouda.life" and password "short"
    Then the action fails with invalid password error "short"

  Scenario: User cannot register twice with the same email address
    Given there are no registered users
    When user registers with email address "ondrej@bouda.life" and password "secret123"
    And user registers with email address "ondrej@bouda.life" and password "secret123"
    Then the action fails with already registered error

  Scenario: User can start multiple sessions
    Given there is a previously registered user that registered with username "ondrej@bouda.life" and password "secret123"
    When user starts a session with email address "ondrej@bouda.life" and password "secret123"
    And user starts a session with email address "ondrej@bouda.life" and password "secret123"
    Then there are two different sessions started for user "ondrej@bouda.life"

  Scenario: User cannot start a session with incorrect password
    Given there is a previously registered user that registered with username "ondrej@bouda.life" and password "secret123"
    When user starts a session with email address "ondrej@bouda.life" and password "bad"
    Then the action fails as not authorized

  Scenario: Different users can start session
    Given there is a previously registered user that registered with username "marie@example.com" and password "secret123"
    And there is a previously registered user that registered with username "kamil@example.com" and password "secret567"
    When user starts a session with email address "marie@example.com" and password "secret123"
    And user starts a session with email address "kamil@example.com" and password "secret567"
    Then user session is started for user "marie@example.com"
    And user session is started for user "kamil@example.com"
