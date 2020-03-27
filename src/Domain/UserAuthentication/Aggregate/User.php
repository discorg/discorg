<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\UserPasswordHash;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;

final class User
{
    private EmailAddress $emailAddress;
    private UserPasswordHash $passwordHash;
    private UserSessionCollection $sessions;

    private function __construct(EmailAddress $emailAddress, UserPasswordHash $passwordHash)
    {
        $this->emailAddress = $emailAddress;
        $this->passwordHash = $passwordHash;
        $this->sessions = UserSessionCollection::empty();
    }

    /**
     * @throws CannotRegisterUser
     */
    public static function register(
        EmailAddress $emailAddress,
        PlaintextUserPassword $password,
        IsUserRegistered $isUserRegistered,
        PasswordHashing $hashing
    ) : self {
        if ($isUserRegistered($emailAddress)) {
            throw CannotRegisterUser::emailAddressAlreadyRegistered($emailAddress);
        }

        $passwordHash = UserPasswordHash::fromPassword($password, $hashing);

        return new self($emailAddress, $passwordHash);
    }

    public function startSession(
        UserSessionToken $token,
        DateTimeImmutable $at
    ) : void {
        $session = UserSession::create($token, $at);

        $this->sessions = $this->sessions->with($session);
    }

    /**
     * @throws SessionNotFound
     * @throws CannotModifySession
     */
    public function renewSession(UserSessionToken $token, DateTimeImmutable $at) : void
    {
        $this->sessions->get($token)->renew($at);
    }

    /**
     * @throws SessionNotFound
     * @throws CannotModifySession
     */
    public function endSession(UserSessionToken $token, DateTimeImmutable $at) : void
    {
        $this->sessions->get($token)->end($at);
    }

    public static function fromStoredValues(EmailAddress $emailAddress, UserPasswordHash $passwordHash) : self
    {
        return new self($emailAddress, $passwordHash);
    }

    /**
     * @throws UserCannotBeAuthenticated
     */
    public function getAuthenticatedIdentifier(
        PlaintextUserPassword $password,
        PasswordHashing $passwordHashing
    ) : AuthenticatedUserIdentifier {
        if (! $this->passwordHash->matches($password, $passwordHashing)) {
            throw UserCannotBeAuthenticated::passwordDoesNotMatch();
        }

        return AuthenticatedUserIdentifier::fromEmailAddress($this->emailAddress);
    }

    /**
     * @throws UserCannotBeAuthenticated
     */
    public function getAuthenticatedIdentifierByToken(
        UserSessionToken $token,
        DateTimeImmutable $at
    ) : AuthenticatedUserIdentifier {
        if (! $this->isAuthenticatedByToken($token, $at)) {
            throw UserCannotBeAuthenticated::passwordDoesNotMatch();
        }

        return AuthenticatedUserIdentifier::fromEmailAddress($this->emailAddress);
    }

    public function isAuthenticatedByToken(UserSessionToken $token, DateTimeImmutable $at) : bool
    {
        return $this->sessions->hasValid($token, $at);
    }

    public function getEmailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }
}
