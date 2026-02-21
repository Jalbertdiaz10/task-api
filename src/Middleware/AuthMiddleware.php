<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Token no proporcionado'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Extrae el token (formato: "Bearer <token>")
        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            
            // Agrega el user_id al request para usarlo en los controladores
            $request = $request->withAttribute('user_id', $decoded->sub);
            
            return $handler->handle($request);
            
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Token inválido o expirado'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}