<?php

declare(strict_types=1);

namespace App\Spotify\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface InitializableSpotifySessionManager
{
    public function initialize(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface;
}
