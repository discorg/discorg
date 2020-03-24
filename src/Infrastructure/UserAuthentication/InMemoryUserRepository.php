<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\EmailAddress;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;
use function array_key_exists;

final class InMemoryUserRepository implements UserRepository
{
    /** @var User[] */
    private array $usersByEmailAddress = [];

    public function save(User $user) : void
    {
        $this->usersByEmailAddress[$user->getEmailAddress()->toString()] = $user;
    }

    public function get(EmailAddress $emailAddress) : User
    {
        if (! array_key_exists($emailAddress->toString(), $this->usersByEmailAddress)) {
            throw UserNotFound::byEmailAddress($emailAddress);
        }

        return $this->usersByEmailAddress[$emailAddress->toString()];
    }

    public function getByValidSessionToken(UserSessionToken $token, DateTimeImmutable $at) : User
    {
        foreach ($this->usersByEmailAddress as $user) {
            if ($user->isAuthenticatedByToken($token, $at)) {
                return $user;
            }
        }

        throw UserNotFound::byToken($token);
    }
}
