<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Redis;
use RedisException;

class HealthCheckController
{
    private Capsule $capsule;
    private Redis $redis;

    public function __construct(Capsule $capsule, Redis $redis)
    {
        $this->capsule = $capsule;
        $this->redis = $redis;
    }

    public function __invoke(Response $response): Response
    {
        $status = ['api_status' => 'ok'];
        $httpStatus = 200;

        try {
            $this->capsule->getConnection()->getPdo()->query('SELECT 1');
            $status['database'] = 'ok';
        } catch (\Exception $e) {
            $status['database'] = 'error';
            $status['database_message'] = $e->getMessage();
            $httpStatus = 500;
        }

        if ($httpStatus === 200) {
            try {
                $redisTestMessage = 'connected';
                $pingResult = $this->redis->ping($redisTestMessage);

                if ($pingResult === $redisTestMessage) {
                    $status['redis'] = 'ok';
                } else {
                    $status['redis'] = 'warning';
                    $status['redis_message'] = 'Redis ping returned unexpected result.';
                    $httpStatus = 500;
                }
            } catch (RedisException $e) {
                $status['redis'] = 'error';
                $status['redis_message'] = $e->getMessage();
                $httpStatus = 500;
            }
        }

        $response->getBody()->write(json_encode($status));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($httpStatus);
    }
}