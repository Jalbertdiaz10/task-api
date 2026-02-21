<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Task;

class TaskController
{
    // GET /tasks - Lista tareas con filtros y paginación
    public function index(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('user_id');
        $params = $request->getQueryParams();

        $query = Task::where('user_id', $userId);

        // Filtro por status
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Filtro por fecha límite
        if (!empty($params['due_date'])) {
            $query->whereDate('due_date', $params['due_date']);
        }

        // Paginación
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $perPage = isset($params['per_page']) ? (int)$params['per_page'] : 10;

        $total = $query->count();
        $tasks = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $tasks,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ]
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    // POST /tasks - Crear tarea
    public function store(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('user_id');
        $data = $request->getParsedBody();

        if (empty($data['title'])) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'El título es obligatorio'
            ]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        $task = Task::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? Task::STATUS_PENDING,
            'due_date' => $data['due_date'] ?? null,
        ]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Tarea creada exitosamente',
            'data' => $task
        ]));

        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    // GET /tasks/{id} - Ver tarea específica
    public function show(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('user_id');
        $taskId = (int)$args['id'];

        $task = Task::where('id', $taskId)->where('user_id', $userId)->first();

        if (!$task) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Tarea no encontrada'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $task
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    // PUT /tasks/{id} - Editar tarea
    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('user_id');
        $taskId = (int)$args['id'];
        $data = $request->getParsedBody();

        $task = Task::where('id', $taskId)->where('user_id', $userId)->first();

        if (!$task) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Tarea no encontrada'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $task->update(array_filter([
            'title' => $data['title'] ?? $task->title,
            'description' => $data['description'] ?? $task->description,
            'status' => $data['status'] ?? $task->status,
            'due_date' => $data['due_date'] ?? $task->due_date,
        ]));

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Tarea actualizada exitosamente',
            'data' => $task->fresh()
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    // DELETE /tasks/{id} - Eliminar tarea
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('user_id');
        $taskId = (int)$args['id'];

        $task = Task::where('id', $taskId)->where('user_id', $userId)->first();

        if (!$task) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Tarea no encontrada'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $task->delete();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Tarea eliminada exitosamente'
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}