<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\HealthCheckController;

return function (App $app): void {
    $app->get('/health', HealthCheckController::class)
        ->setName('health');
};
