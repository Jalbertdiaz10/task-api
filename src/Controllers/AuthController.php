<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use Firebase\JWT\JWT;

class AuthController
{
    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validaciones básicas
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Todos los campos son obligatorios'
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        // Verifica si el email ya existe
        if (User::where('email', $data['email'])->exists()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'El email ya está registrado'
            ]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        // Crea el usuario
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]));

        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validaciones
        if (empty($data['email']) || empty($data['password'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Email y contraseña son obligatorios'
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        // Busca el usuario
        $user = User::where('email', $data['email'])->first();

        if (!$user || !password_verify($data['password'], $user->password)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Credenciales incorrectas'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Genera el JWT
        $payload = [
            'iss' => 'task-api',
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (int)$_ENV['JWT_EXPIRATION'],
        ];

        $jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'token' => $jwt,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]
        ]));

        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}