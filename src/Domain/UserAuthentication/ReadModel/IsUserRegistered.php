<?php

declare(strict_types=1);

namespace App\Domain\UserAuthentication\ReadModel;

use App\Domain\UserAuthentication\EmailAddress;

interface IsUserRegistered
{
    public function __invoke(EmailAddress $emailAddress) : bool;
}
