<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\TaskController;
use App\Middleware\AuthMiddleware;

return function (App $app) {
    $app->group('/tasks', function ($group) {
        $group->get('', [TaskController::class, 'index']);
        $group->post('', [TaskController::class, 'store']);
        $group->get('/{id:[0-9]+}', [TaskController::class, 'show']);
        $group->put('/{id:[0-9]+}', [TaskController::class, 'update']);
        $group->delete('/{id:[0-9]+}', [TaskController::class, 'destroy']);
    })->add(AuthMiddleware::class);
};