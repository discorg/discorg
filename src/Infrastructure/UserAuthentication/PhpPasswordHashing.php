<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAuthentication;

use App\Domain\UserAuthentication\PasswordHashing;
use LogicException;
use const PASSWORD_DEFAULT;
use function password_hash;
use function password_verify;

final class PhpPasswordHashing implements PasswordHashing
{
    public function hash(string $password) : string
    {
        $result = password_hash($password, PASSWORD_DEFAULT);

        if ($result === false) {
            throw new LogicException('Function password_hash() failed.');
        }

        return $result;
    }

    public function verify(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
    }
}
