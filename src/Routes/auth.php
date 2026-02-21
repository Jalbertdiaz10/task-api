<?php

declare(strict_types=1);

use Slim\App;
use App\Controllers\AuthController;

return function (App $app) {
    $app->group('/auth', function ($group) {
        $group->post('/register', [AuthController::class, 'register']);
        $group->post('/login', [AuthController::class, 'login']);
    });
};