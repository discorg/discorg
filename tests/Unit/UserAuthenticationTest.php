<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\UserAuthentication\IsUserAuthenticated;
use App\Application\UserAuthentication\RegisterUser;
use App\Domain\UserAuthentication\Aggregate\CannotRegisterUser;
use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\PlaintextUserPassword;
use App\Domain\UserAuthentication\ReadModel\IsUserRegistered;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserCredentials;
use App\Domain\UserAuthentication\UserPasswordHash;
use App\Infrastructure\UserAuthentication\PhpPasswordHashing;
use LogicException;
use PHPUnit\Framework\TestCase;

final class UserAuthenticationTest extends TestCase
{
    public function testUserCanRegisterAndAfterwardsIsAuthenticated() : void
    {
        $isUserRegistered = new class implements IsUserRegistered
        {
            public function __invoke(EmailAddress $emailAddress) : bool
            {
                return false;
            }
        };

        $userRepository = new class implements UserRepository
        {
            private ?User $savedUser = null;

            public function save(User $user) : void
            {
                $this->savedUser = $user;
            }

            /**
             * @throws void
             */
            public function get(EmailAddress $emailAddress) : User
            {
                if ($this->savedUser === null) {
                    throw new LogicException('User should have been saved.');
                }

                return $this->savedUser;
            }
        };

        $register = new RegisterUser($isUserRegistered, $userRepository, new PhpPasswordHashing());
        $isUserAuthenticated = new IsUserAuthenticated($userRepository, new PhpPasswordHashing());

        $credentials = UserCredentials::fromStrings('ondrej@sample.com', '1234567');

        $register->__invoke($credentials);

        self::assertTrue($isUserAuthenticated->__invoke($credentials));
    }

    public function testUserRegistrationFailsWhenEmailAddressAlreadyRegistered() : void
    {
        $isUserRegistered = new class implements IsUserRegistered
        {
            public function __invoke(EmailAddress $emailAddress) : bool
            {
                return true;
            }
        };

        $userRepository = new class implements UserRepository
        {
            public function save(User $user) : void
            {
                throw new LogicException('Should not be called.');
            }

            /**
             * @throws void
             */
            public function get(EmailAddress $emailAddress) : User
            {
                throw new LogicException('Should not be called.');
            }
        };

        $register = new RegisterUser($isUserRegistered, $userRepository, new PhpPasswordHashing());

        $credentials = UserCredentials::fromStrings('ondrej@sample.com', '1234567');

        self::expectException(CannotRegisterUser::class);
        $register->__invoke($credentials);
    }

    public function testUserIsNotAuthenticatedWhenEmailAddressNotFound() : void
    {
        $userRepository = new class implements UserRepository
        {
            public function save(User $user) : void
            {
                throw new LogicException('Should not be called.');
            }

            /**
             * @throws void
             */
            public function get(EmailAddress $emailAddress) : User
            {
                throw UserNotFound::byEmailAddress($emailAddress);
            }
        };

        $isUserAuthenticated = new IsUserAuthenticated($userRepository, new PhpPasswordHashing());

        $credentials = UserCredentials::fromStrings('ondrej@sample.com', '1234567');

        self::assertFalse($isUserAuthenticated->__invoke($credentials));
    }

    public function testUserIsNotAuthenticatedWithWrongPassword() : void
    {
        $userRepository = new class implements UserRepository
        {
            public function save(User $user) : void
            {
                throw new LogicException('Should not be called.');
            }

            /**
             * @throws void
             */
            public function get(EmailAddress $emailAddress) : User
            {
                $password = PlaintextUserPassword::fromString('abcdefgh');
                $passwordHash =  UserPasswordHash::fromPassword($password, new PhpPasswordHashing());

                return User::fromStoredValues($emailAddress, $passwordHash);
            }
        };

        $isUserAuthenticated = new IsUserAuthenticated($userRepository, new PhpPasswordHashing());

        $credentials = UserCredentials::fromStrings('ondrej@sample.com', '1234567');

        self::assertFalse($isUserAuthenticated->__invoke($credentials));
    }
}
