<?php

declare(strict_types=1);

namespace App\User;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface InitializableUserSessionManager
{
    public function initialize(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface;
}
