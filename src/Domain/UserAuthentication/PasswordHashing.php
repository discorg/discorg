<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication;

interface PasswordHashing
{
    public function hash(string $password) : string;

    public function verify(string $password, string $hash) : bool;
}
