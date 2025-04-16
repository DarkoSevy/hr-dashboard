<?php
require_once __DIR__ . '/../../models/Employee.php';

try {
    // Parse the request URI to get the employee ID if present
    $request_uri = $_SERVER['REQUEST_URI'];
    $base_path = '/api/employees';
    $path = parse_url($request_uri, PHP_URL_PATH);
    $path = rtrim($path, '/');

    // Extract ID from path if present
    $id = null;
    if (preg_match('#^' . preg_quote($base_path) . '/(\d+)$#', $path, $matches)) {
        $id = $matches[1];
    }

    $employee = new Employee();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if ($id) {
                $result = $employee->getById($id);
                if ($result) {
                    http_response_code(200);
                    echo json_encode(['status' => 'success', 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Employee not found']);
                }
            } else {
                $result = $employee->getAll();
                http_response_code(200);
                echo json_encode(['status' => 'success', 'data' => $result]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
                break;
            }
            
            if ($employee->create($data)) {
                http_response_code(201);
                echo json_encode(['status' => 'success', 'message' => 'Employee created successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Unable to create employee']);
            }
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No employee ID provided']);
                break;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
                break;
            }
            
            if ($employee->update($id, $data)) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Employee updated successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Unable to update employee']);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'No employee ID provided']);
                break;
            }
            
            if ($employee->delete($id)) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Employee deleted successfully']);
            } else {
                http_response_code(503);
                echo json_encode(['status' => 'error', 'message' => 'Unable to delete employee']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log('Employee API Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'error' => $e->getMessage()
    ]);
} 