Feature: User can register and manage their sessions
  In order to use the application
  as a user
  I must be able to authenticate.

  Scenario: User can register and start a session
    Given there are no registered users
    When user registers with email address "ondrej@bouda.life" and password "secret123"
    And user starts a session with email address "ondrej@bouda.life" and password "secret123"
    Then user session is started for user "ondrej@bouda.life"
