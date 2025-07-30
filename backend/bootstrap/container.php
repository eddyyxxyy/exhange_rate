<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Config\AppConfig;
use Redis;

return [

    AppConfig::class => fn() => new AppConfig(require __DIR__ . '/../config/app.php'),

    Redis::class => function (ContainerInterface $c): Redis {
        $config = $c->get(AppConfig::class)->get('redis');
        $redis = new Redis();
        $redis->connect($config['host'], $config['port']);
        $redis->auth($config['auth']);
        return $redis;
    },

    ResponseFactoryInterface::class => fn() => new ResponseFactory(),

];
