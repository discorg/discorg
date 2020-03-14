<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PasswordHashing;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\ReadModel\IsUserRegistered;
use App\Domain\UserAuthentication\UserCredentials;
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
        UserCredentials $credentials,
        IsUserRegistered $isUserRegistered,
        PasswordHashing $hashing
    ) : self {
        if ($isUserRegistered($credentials->emailAddress())) {
            throw CannotRegisterUser::emailAddressAlreadyRegistered($credentials->emailAddress());
        }

        $passwordHash = UserPasswordHash::fromPassword($credentials->password(), $hashing);

        return new self($credentials->emailAddress(), $passwordHash);
    }

    public function startSession(
        UserSessionToken $token,
        DateTimeImmutable $at
    ) : void {
        $session = UserSession::create($token, $at);

        $this->sessions->with($session);
    }

    public static function fromStoredValues(EmailAddress $emailAddress, UserPasswordHash $passwordHash) : self
    {
        return new self($emailAddress, $passwordHash);
    }

    public function isAuthenticatedBy(PlaintextUserPassword $password, PasswordHashing $passwordHashing) : bool
    {
        return $this->passwordHash->matches($password, $passwordHashing);
    }

    public function getEmailAddress() : EmailAddress
    {
        return $this->emailAddress;
    }
}
