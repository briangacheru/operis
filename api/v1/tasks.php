<?php
declare(strict_types=1);

/**
 * /api/v1/tasks
 *
 * GET    /tasks           — list tasks for authenticated writer
 * GET    /tasks/{id}      — single task (must belong to writer)
 * POST   /tasks           — create task
 * PATCH  /tasks/{id}      — update status / acknowledge
 * DELETE /tasks/{id}      — soft-delete
 */

require_once dirname(__DIR__, 2) . '/includes/Service/TaskService.php';

$email   = requireAuth();
$service = new TaskService();

switch ($method) {

    // -----------------------------------------------------------------------
    case 'GET':
    // -----------------------------------------------------------------------
        if ($id !== null) {
            $task = $service->getTaskForWriter($email, $id);
            if (!$task) apiResponse(['error' => 'Not found'], 404);
            apiResponse($task);
        }

        $status = $_GET['status'] ?? '';
        $tasks  = $service->getTasksForWriter($email, $status);
        apiResponse(['data' => $tasks, 'total' => count($tasks)]);

    // -----------------------------------------------------------------------
    case 'POST':
    // -----------------------------------------------------------------------
        try {
            $newId = $service->createTask($body, $email);
            apiResponse(['id' => $newId, 'message' => 'Task created.'], 201);
        } catch (InvalidArgumentException $e) {
            apiResponse(['error' => $e->getMessage()], 422);
        }

    // -----------------------------------------------------------------------
    case 'PATCH':
    // -----------------------------------------------------------------------
        if ($id === null) apiResponse(['error' => 'Task ID required'], 400);

        try {
            if (isset($body['status'])) {
                $service->updateStatus($id, $body['status'], $email);
            }
            if (!empty($body['acknowledge'])) {
                $service->acknowledgeTask($id);
            }
            apiResponse(['message' => 'Updated.']);
        } catch (InvalidArgumentException $e) {
            apiResponse(['error' => $e->getMessage()], 422);
        }

    // -----------------------------------------------------------------------
    case 'DELETE':
    // -----------------------------------------------------------------------
        if ($id === null) apiResponse(['error' => 'Task ID required'], 400);

        $task = $service->getTaskForWriter($email, $id);
        if (!$task) apiResponse(['error' => 'Not found'], 404);

        $repo = new TaskRepository();
        $repo->softDelete($id);
        apiResponse(['message' => 'Task deleted.']);

    // -----------------------------------------------------------------------
    default:
        apiResponse(['error' => 'Method not allowed'], 405);
}
