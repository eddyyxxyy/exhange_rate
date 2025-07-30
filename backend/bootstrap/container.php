<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Config\AppConfig;
use Redis;
use function DI\factory;
use function DI\autowire;
use function DI\get;

return [
    'config.app' => require __DIR__ . '/../config/app.php',

    AppConfig::class => autowire()
        ->constructorParameter('config', get('config.app')),

    Redis::class => factory(function (ContainerInterface $c): Redis {
        /** @var AppConfig $appConfig */
        $appConfig = $c->get(AppConfig::class);
        $config = $appConfig->get('redis');

        $redis = new Redis();
        $redis->connect($config['host'], $config['port']);
        if (!empty($config['auth'])) {
            $redis->auth($config['auth']);
        }
        return $redis;
    }),

    ResponseFactoryInterface::class => autowire(ResponseFactory::class),
];
