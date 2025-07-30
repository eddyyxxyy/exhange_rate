<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;

class HealthCheckController
{
    public function __invoke(Response $response): Response
    {
        $response->getBody()->write(json_encode(['status' => 'ok']));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
