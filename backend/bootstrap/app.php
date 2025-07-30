<?php

use DI\ContainerBuilder;
use DI\Bridge\Slim\Bridge;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

$container = $containerBuilder->build();

$app = Bridge::create($container);

$app->setBasePath('/api');

(require __DIR__ . '/routes.php')($app);

return $app;
