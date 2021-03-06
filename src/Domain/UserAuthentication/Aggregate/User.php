<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\AuthenticatedUserId;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\UserId;
use App\Domain\UserAuthentication\Username;
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
        $username = Username::fromEmailAddress($emailAddress);
        if ($isUserRegistered->__invoke($username)) {
            throw CannotRegisterUser::emailAddressAlreadyRegistered($emailAddress);
        }

        $passwordHash = UserPasswordHash::fromPassword($password, $hashing);

        return new self($emailAddress, $passwordHash);
    }

    public function startSession(
        UserSessionToken $tokenCandidate,
        DateTimeImmutable $at
    ) : void {
        $session = UserSession::create($tokenCandidate, $at);

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
    public function authenticateByCredentials(
        PlaintextUserPassword $password,
        PasswordHashing $passwordHashing
    ) : AuthenticatedUserId {
        if (! $this->passwordHash->matches($password, $passwordHashing)) {
            throw UserCannotBeAuthenticated::passwordDoesNotMatch();
        }

        return AuthenticatedUserId::fromId($this->id());
    }

    /**
     * @throws UserCannotBeAuthenticated
     */
    public function authenticateByToken(
        UserSessionToken $token,
        DateTimeImmutable $at
    ) : AuthenticatedUserId {
        if (! $this->isAuthenticatedByToken($token, $at)) {
            throw UserCannotBeAuthenticated::passwordDoesNotMatch();
        }

        return AuthenticatedUserId::fromId($this->id());
    }

    public function isAuthenticatedByToken(UserSessionToken $token, DateTimeImmutable $at) : bool
    {
        return $this->sessions->hasValid($token, $at);
    }

    /**
     * @internal
     */
    public function id() : UserId
    {
        return UserId::fromString($this->emailAddress->toString());
    }

    /**
     * @internal
     */
    public function matchesUsername(Username $username) : bool
    {
        return $this->emailAddress->toString() === $username->toString();
    }

    /**
     * TODO: get rid of this, use separate read model
     */
    public function emailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }
}
