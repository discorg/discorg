<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\UserAuthentication\Aggregate\CannotModifySession;
use App\Domain\UserAuthentication\Aggregate\IsUserRegistered;
use App\Domain\UserAuthentication\Aggregate\SessionNotFound;
use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\UserSessionToken;
use App\Infrastructure\UserAuthentication\PhpPasswordHashing;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserSessionTest extends TestCase
{
    public function testStartSession() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        self::assertTrue($user->isAuthenticatedByToken($token, $now));
    }

    public function testAuthenticateByToken() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        self::assertTrue($user->isAuthenticatedByToken($token, $now->modify('+ 1 hour')));
        self::assertTrue($user->isAuthenticatedByToken($token, $now->modify('+ 12 hour')));
        self::assertTrue($user->isAuthenticatedByToken($token, $now->modify('+ 24 hour')));

        self::assertFalse($user->isAuthenticatedByToken($token, $now->modify('+ 25 hour')));
    }

    public function testRenewSessionRepeatedly() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $later = new DateTimeImmutable('2020-03-03 14:00:00');
        $user->renewSession($token, $later);

        $laterStill = new DateTimeImmutable('2020-03-04 13:00:00');

        self::assertTrue($user->isAuthenticatedByToken($token, $laterStill));
    }

    public function testRenewSessionFailsWhenNotFound() : void
    {
        $user = $this->createUser();
        $token = UserSessionToken::generate();

        $later = new DateTimeImmutable('2020-03-04 14:00:00');

        $this->expectException(SessionNotFound::class);
        $user->renewSession($token, $later);
    }

    public function testRenewSessionFailsWhenAlreadyExpired() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $later = new DateTimeImmutable('2020-03-04 14:00:00');

        $this->expectException(CannotModifySession::class);
        $user->renewSession($token, $later);
    }

    public function testRenewSessionFailsWhenAlreadyEnded() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $user->endSession($token, $now);

        $later = new DateTimeImmutable('2020-03-03 14:00:00');

        $this->expectException(CannotModifySession::class);
        $user->renewSession($token, $later);
    }

    public function testEndSession() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $user->endSession($token, $now);

        self::assertFalse($user->isAuthenticatedByToken($token, $now));
    }

    public function testEndSessionFailsWhenCalledRepeatedly() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $user->endSession($token, $now);

        $this->expectException(CannotModifySession::class);
        $user->endSession($token, $now);
    }

    public function testEndSessionFailsWhenSessionNotFound() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $user->endSession($token, $now);

        $this->expectException(CannotModifySession::class);
        $user->endSession($token, $now);
    }

    public function testEndSessionFailsWhenAlreadyExpired() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $later = new DateTimeImmutable('2020-03-04 14:00:00');

        $this->expectException(CannotModifySession::class);
        $user->endSession($token, $later);
    }

    public function testEndSessionFailsWhenAlreadyEnded() : void
    {
        $now = new DateTimeImmutable('2020-03-03 12:00:00');

        $user = $this->createUser();
        $token = UserSessionToken::generate();
        $user->startSession($token, $now);

        $user->endSession($token, $now);

        $later = new DateTimeImmutable('2020-03-03 14:00:00');

        $this->expectException(CannotModifySession::class);
        $user->endSession($token, $later);
    }

    private function createUser() : User
    {
        return User::register(
            EmailAddress::fromString('mario@napoli.it'),
            PlaintextUserPassword::fromString('amatriciana'),
            $this->fakeIsUserRegistered(),
            new PhpPasswordHashing(),
        );
    }

    private function fakeIsUserRegistered() : IsUserRegistered
    {
        return new class implements IsUserRegistered
        {
            public function __invoke(EmailAddress $emailAddress) : bool
            {
                return false;
            }
        };
    }
}
