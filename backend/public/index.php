<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->setBasePath('/api');

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello from Slim in /api");
    return $response;
});

// Test endpoints
$app->get('/ping', function (Request $request, Response $response, array $args) {
    $response->getBody()->write(json_encode(['message' => 'pong']));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->get('/ping/{name}', function (Request $request, Response $response, array $args) {
    $response->getBody()->write(json_encode(['message' => 'pong for ' . $args['name']]));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});
// End test endpoints

$app->run();
