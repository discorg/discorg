Feature: User can register and manage their sessions
  In order to use the application
  as a user
  I must be able to authenticate.

  Scenario: User can register and start a session
    Given there are no registered users
    When user registers with email address "ondrej@bouda.life" and password "secret123"
    And user starts a session with email address "ondrej@bouda.life" and password "secret123"
    Then user session is started for user "ondrej@bouda.life"

  Scenario: User cannot register twice with the same email address
    Given there are no registered users
    When user registers with email address "ondrej@bouda.life" and password "secret123"
    Then another registration with email address "ondrej@bouda.life" fails

  Scenario: User can start multiple sessions
    Given there is a previously registered user that registered with username "ondrej@bouda.life" and password "secret123"
    When user starts a session with email address "ondrej@bouda.life" and password "secret123"
    And user starts a session with email address "ondrej@bouda.life" and password "secret123"
    Then there are two different sessions started for user "ondrej@bouda.life"

  Scenario: User cannot log in with incorrect password
    Given there is a previously registered user that registered with username "ondrej@bouda.life" and password "secret123"
    Then starting a session with email address "ondrej@bouda.life" and password "bad" fails
