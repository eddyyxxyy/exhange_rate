<?php

return [
    'app_env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',

    'db' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'test',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
        'collation' => $_ENV['DB_COLLATION'] ?? 'utf8_unicode_ci',
        'prefix' => $_ENV['DB_PREFIX'] ?? '',
    ],

    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'auth' => $_ENV['REDIS_AUTH'] ?? null,
    ],
];
