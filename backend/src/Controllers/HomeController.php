<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;

class HomeController
{
    public function index(Response $response): Response
    {
        $response->getBody()->write("Hello from Slim in /api");
        return $response;
    }
}