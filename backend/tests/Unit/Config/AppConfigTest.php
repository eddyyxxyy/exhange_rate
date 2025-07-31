<?php

declare(strict_types=1);

use App\Config\AppConfig;

/**
 * @covers \App\Config\AppConfig::get
 */
it('can retrieve config values by dot notation', function () {
    $configData = [
        'app_env' => 'testing',
        'db' => [
            'host' => 'localhost',
            'name' => 'test_db'
        ]
    ];
    $appConfig = new AppConfig($configData);

    expect($appConfig->get('app_env'))->toBe('testing');
    expect($appConfig->get('db.host'))->toBe('localhost');
    expect($appConfig->get('db.name'))->toBe('test_db');
});

/**
 * @covers \App\Config\AppConfig::get
 */
it('returns default value for non-existent keys', function () {
    $configData = ['some' => 'value'];
    $appConfig = new AppConfig($configData);

    expect($appConfig->get('non.existent.key'))->toBeNull();
    expect($appConfig->get('another.key', 'default_value'))->toBe('default_value');
});

/**
 * @covers \App\Config\AppConfig::all
 */
it('returns all config values', function () {
    $configData = ['key1' => 'value1', 'key2' => 'value2'];
    $appConfig = new AppConfig($configData);

    expect($appConfig->all())->toBe($configData);
});