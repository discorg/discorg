<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAuthentication;

use App\Domain\UserAuthentication\Aggregate\User;
use App\Domain\UserAuthentication\AuthenticatedUserIdentifier;
use App\Domain\UserAuthentication\Repository\UserNotFound;
use App\Domain\UserAuthentication\Repository\UserRepository;
use App\Domain\UserAuthentication\Username;
use App\Domain\UserAuthentication\UserSessionToken;
use DateTimeImmutable;
use function array_key_exists;

final class InMemoryUserRepository implements UserRepository
{
    /** @var User[] */
    private array $usersById = [];

    public function save(User $user) : void
    {
        $this->usersById[$user->id()->toString()] = $user;
    }

    public function get(AuthenticatedUserIdentifier $id) : User
    {
        if (! array_key_exists($id->toString(), $this->usersById)) {
            throw UserNotFound::byId($id);
        }

        return $this->usersById[$id->toString()];
    }

    public function getByUsername(Username $username) : User
    {
        foreach ($this->usersById as $user) {
            if ($user->matchesUsername($username)) {
                return $user;
            }
        }

        throw UserNotFound::byUsername($username);
    }

    public function getByValidSessionToken(UserSessionToken $token, DateTimeImmutable $at) : User
    {
        foreach ($this->usersById as $user) {
            if ($user->isAuthenticatedByToken($token, $at)) {
                return $user;
            }
        }

        throw UserNotFound::byToken($token);
    }
}
