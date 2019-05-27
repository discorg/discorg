<?php

declare(strict_types=1);

namespace Bouda\SpotifyAlbumTagger\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Action
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface;
}
