<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Actions;

use App\Infrastructure\Http\Action;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeAction implements Action
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $responseBody = (new Psr17Factory())->createStream('Hello world');

        return $response->withStatus(200)->withBody($responseBody);
    }
}
