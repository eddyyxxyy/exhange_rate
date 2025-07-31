<?php

declare(strict_types=1);

use App\Controllers\HealthCheckController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Mockery;

/**
 * @covers \App\Controllers\HealthCheckController::__invoke
 */
it('returns a 200 OK status with JSON content type', function () {
    $mockResponse = Mockery::mock(ResponseInterface::class);
    $mockStream = Mockery::mock(StreamInterface::class);

    $mockResponse->shouldReceive('getBody')->andReturn($mockStream);
    $mockStream->shouldReceive('write')->once()->with(json_encode(['status' => 'ok']));
    $mockResponse->shouldReceive('withHeader')->with('Content-Type', 'application/json')->andReturnSelf();
    $mockResponse->shouldReceive('withStatus')->with(200)->andReturnSelf();

    $controller = new HealthCheckController();
    $result = $controller($mockResponse);

    expect($result)->toBeInstanceOf(ResponseInterface::class);
});