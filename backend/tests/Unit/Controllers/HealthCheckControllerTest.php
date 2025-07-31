<?php

declare(strict_types=1);

use App\Controllers\HealthCheckController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

/**
 * @covers \App\Controllers\HealthCheckController::__invoke
 */
it('returns a 200 OK status with all dependencies healthy', function () {
    $mockResponse = \Mockery::mock(ResponseInterface::class);
    $mockStream = \Mockery::mock(StreamInterface::class);

    $mockCapsule = \Mockery::mock(Capsule::class);
    $mockConnection = \Mockery::mock(Connection::class);
    $mockPdo = \Mockery::mock(\PDO::class);
    $mockRedis = \Mockery::mock(\Redis::class);

    $mockCapsule->shouldReceive('getConnection')->once()->andReturn($mockConnection);
    $mockConnection->shouldReceive('getPdo')->once()->andReturn($mockPdo);
    $mockPdo->shouldReceive('query')->once()->with('SELECT 1');

    $redisTestMessage = 'connected';
    $mockRedis->shouldReceive('ping')->once()->with($redisTestMessage)->andReturn($redisTestMessage);

    $mockResponse->shouldReceive('getBody')->once()->andReturn($mockStream);
    $mockStream->shouldReceive('write')->once()->with(json_encode([
        'api_status' => 'ok',
        'database' => 'ok',
        'redis' => 'ok'
    ]));
    $mockResponse->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();
    $mockResponse->shouldReceive('withStatus')->with(200)->andReturnSelf();

    $controller = new HealthCheckController($mockCapsule, $mockRedis);
    $result = $controller($mockResponse);

    expect($result)->toBeInstanceOf(ResponseInterface::class);
});

it('returns a 500 status when database check fails', function () {
    $mockResponse = \Mockery::mock(ResponseInterface::class);
    $mockStream = \Mockery::mock(StreamInterface::class);

    $mockCapsule = \Mockery::mock(Capsule::class);
    $mockConnection = \Mockery::mock(Connection::class);
    $mockPdo = \Mockery::mock(\PDO::class);
    $mockRedis = \Mockery::mock(\Redis::class);

    $mockCapsule->shouldReceive('getConnection')->once()->andReturn($mockConnection);
    $mockConnection->shouldReceive('getPdo')->once()->andReturn($mockPdo);
    $mockPdo->shouldReceive('query')->once()->with('SELECT 1')->andThrow(new \Exception('DB Connection Error'));

    $mockRedis->shouldNotReceive('ping');

    $mockResponse->shouldReceive('getBody')->once()->andReturn($mockStream);
    $mockStream->shouldReceive('write')->once()->with(json_encode([
        'api_status' => 'ok',
        'database' => 'error',
        'database_message' => 'DB Connection Error'
    ]));
    $mockResponse->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();
    $mockResponse->shouldReceive('withStatus')->with(500)->andReturnSelf();

    $controller = new HealthCheckController($mockCapsule, $mockRedis);
    $result = $controller($mockResponse);

    expect($result)->toBeInstanceOf(ResponseInterface::class);
});