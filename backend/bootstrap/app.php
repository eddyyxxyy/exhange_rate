<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use DI\ContainerBuilder;
use App\Config\AppConfig;
use DI\Bridge\Slim\Bridge;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions(__DIR__ . '/container.php');

$tempContainer = $containerBuilder->build();
$appConfig = $tempContainer->get(AppConfig::class);

if ($appConfig->get('app_env') === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../cache/tmp');
    $containerBuilder->writeProxiesToFile(true, __DIR__ . '/../cache/tmp/proxies');
}

$container = $containerBuilder->build();

$app = Bridge::create($container);

$app->setBasePath('/api');

(require __DIR__ . '/routes.php')($app);

return $app;