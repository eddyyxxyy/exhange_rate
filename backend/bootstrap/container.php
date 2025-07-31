<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;
use App\Config\AppConfig;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Redis;
use function DI\factory;
use function DI\autowire;
use function DI\get;

return [
    'config.app' => require __DIR__ . '/../config/app.php',

    AppConfig::class => autowire()
        ->constructorParameter('config', get('config.app')),

    Capsule::class => factory(function (ContainerInterface $c): Capsule {
        /** @var AppConfig $appConfig */
        $appConfig = $c->get(AppConfig::class);
        $config = $appConfig->get('db');

        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => $config['driver'],
            'host' => $config['host'],
            'database' => $config['name'],
            'username' => $config['user'],
            'password' => $config['pass'],
            'charset' => $config['charset'],
            'collation' => $config['collation'],
            'prefix' => $config['prefix'],
        ]);

        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }),

    PDO::class => function (ContainerInterface $c) {
        $appConfig = $c->get(AppConfig::class);
        $dbConnection = $appConfig->get('db.connection');
        $dbDatabase = $appConfig->get('db.database');
        $dbHost = $appConfig->get('db.host');
        $dbPort = $appConfig->get('db.port');
        $dbUser = $appConfig->get('db.username');
        $dbPass = $appConfig->get('db.password');
        $charset = $appConfig->get('db.charset');

        if ($dbConnection === 'sqlite' && $dbDatabase === ':memory:') {
            $pdo = new PDO('sqlite::memory:');
        } else {
            $dsn = '';
            switch ($dbConnection) {
                case 'mysql':
                    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbDatabase};charset={$charset}";
                    break;
                case 'pgsql':
                    $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbDatabase}";
                    break;
                default:
                    throw new InvalidArgumentException("Unsupported database connection: {$dbConnection}");
            }
            $pdo = new PDO($dsn, $dbUser, $dbPass);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Opcional: define fetch mode padrão
    
        return $pdo;
    },

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
