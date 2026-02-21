<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

// ─── 1. AUTOLOAD ────────────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

// ─── 2. VARIABLES DE ENTORNO ────────────────────────────────────────────────
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'JWT_SECRET']);

// ─── 3. BASE DE DATOS (Eloquent ORM) ────────────────────────────────────────
$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'port'      => $_ENV['DB_PORT'] ?? '3306',
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// ─── 4. CONTENEDOR DE DEPENDENCIAS ──────────────────────────────────────────
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// ─── 5. APLICACIÓN SLIM ──────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();

// ─── 6. MIDDLEWARES GLOBALES ─────────────────────────────────────────────────
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: true,
    logErrors: true,
    logErrorDetails: true
);

$errorMiddleware->setDefaultErrorHandler(
    function (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails
    ) use ($app) {
        $statusCode = 500;

        if ($exception instanceof \Slim\Exception\HttpNotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof \Slim\Exception\HttpMethodNotAllowedException) {
            $statusCode = 405;
        }

        $payload = [
            'success' => false,
            'error'   => $displayErrorDetails
                ? $exception->getMessage()
                : 'Ha ocurrido un error interno.',
        ];

        $response = $app->getResponseFactory()->createResponse($statusCode);
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    }
);

// ─── 7. CABECERAS CORS ───────────────────────────────────────────────────────
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->options('/{routes:.+}', function ($request, $response) {
    return $response->withStatus(200);
});

// ─── 8. RUTAS ────────────────────────────────────────────────────────────────
(require __DIR__ . '/../src/Routes/auth.php')($app);
(require __DIR__ . '/../src/Routes/tasks.php')($app);

// ─── 9. EJECUTAR ─────────────────────────────────────────────────────────────
$app->run();