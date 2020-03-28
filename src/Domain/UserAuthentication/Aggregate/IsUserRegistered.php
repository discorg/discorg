<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\Aggregate;

use App\Domain\UserAuthentication\Username;

interface IsUserRegistered
{
    public function __invoke(Username $username) : bool;
}
