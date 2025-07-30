<?php

use DI\Bridge\Slim\Bridge;
use App\Controllers\HomeController;

require __DIR__ . '/../vendor/autoload.php';

$app = Bridge::create();

$app->setBasePath('/api');

$app->get('/', [HomeController::class, 'index']);

$app->run();
