<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAuthentication;

use App\Domain\UserAuthentication\PasswordHashing;
use const PASSWORD_DEFAULT;
use function password_hash;
use function password_verify;

final class PhpPasswordHashing implements PasswordHashing
{
    public function hash(string $password) : string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
    }
}
