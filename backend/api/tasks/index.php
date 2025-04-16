<?php
// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../models/Task.php';

// Parse the request URI to get the task ID if present
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api/tasks';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = rtrim($path, '/');

// Extract ID from path if present
$id = null;
if (preg_match('#^' . preg_quote($base_path) . '/(\d+)$#', $path, $matches)) {
    $id = $matches[1];
}

$task = new Task();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                $result = $task->getById($id);
                if ($result) {
                    http_response_code(200);
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "Task not found."));
                }
            } else {
                $result = $task->getAll();
                http_response_code(200);
                echo json_encode($result);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(array("message" => "Invalid input data."));
                break;
            }
            
            if ($task->create($data)) {
                http_response_code(201);
                echo json_encode(array("message" => "Task created successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create task."));
            }
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(array("message" => "No task ID provided."));
                break;
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(array("message" => "Invalid input data."));
                break;
            }
            
            if ($task->update($id, $data)) {
                http_response_code(200);
                echo json_encode(array("message" => "Task updated successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update task."));
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(array("message" => "No task ID provided."));
                break;
            }
            
            if ($task->delete($id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Task deleted successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete task."));
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array("message" => "Method not allowed."));
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        "message" => "Server error occurred.",
        "error" => $e->getMessage()
    ));
} 