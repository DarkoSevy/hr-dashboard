<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error handler at the very top after error_reporting
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server Error',
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
});

// Set exception handler
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server Error',
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
});

// Load configuration once
$config = require_once __DIR__ . '/../config/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get the frontend URL from environment variable
$frontendUrl = getenv('FRONTEND_URL') ?: 'http://localhost:3000';

// Define allowed origins
$allowedOrigins = [
    $frontendUrl,
    'http://localhost:3000',
    'http://localhost:5173'
];

// Get the origin of the request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Check if the origin is allowed
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}

require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/JwtMiddleware.php';

// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remove query string and trailing slash
$uri = strtok($uri, '?');
$uri = rtrim($uri, '/');

// Handle preflight requests
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Route the request
try {
    // Setup route for creating admin user
    if ($uri === '/api/setup' && $method === 'GET') {
        try {
            // Create users table if not exists
            $query = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'manager', 'employee') NOT NULL DEFAULT 'employee',
                employee_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $db = new Database();
            $conn = $db->connect();
            $conn->exec($query);
            
            // Create admin user if not exists
            $user = new User();
            $adminData = [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin'
            ];
            
            try {
                $result = $user->create($adminData);
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin user created successfully'
                ]);
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Admin user already exists'
                    ]);
                } else {
                    throw $e;
                }
            }
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Setup failed',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // Authentication routes
    if ($uri === '/api/auth/login' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log('Login attempt with data: ' . json_encode($data));
        
        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Username and password are required'
            ]);
            exit;
        }
        
        try {
            $user = new User();
            $result = $user->authenticate($data['username'], $data['password']);
            error_log('Authentication result: ' . json_encode($result));
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'user' => $result['user'],
                        'token' => $result['token']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid credentials'
                ]);
            }
        } catch (Exception $e) {
            error_log('Authentication error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Authentication failed: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Protected routes
    if (strpos($uri, '/api/') === 0) {
        // Authenticate the request
        $decoded = JwtMiddleware::authenticate();
        
        // Check user permissions based on role
        if (strpos($uri, '/api/admin/') === 0 && $decoded->role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }
    }

    // Existing routes
    if ($uri === '/api/employees' || $uri === 'api/employees') {
        $employee = new Employee();
        
        switch ($method) {
            case 'GET':
                $employees = $employee->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $employees
                ]);
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $employee->create($data);
                
                if ($result) {
                    http_response_code(201);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Employee created successfully'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to create employee'
                    ]);
                }
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    } elseif (preg_match('/^\/api\/employees\/(\d+)$/', $uri, $matches) || 
              preg_match('/^api\/employees\/(\d+)$/', $uri, $matches)) {
        $id = $matches[1];
        $employee = new Employee();
        
        switch ($method) {
            case 'GET':
                $employeeData = $employee->getById($id);
                if ($employeeData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $employeeData
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Employee not found'
                    ]);
                }
                break;
                
            case 'PUT':
                $data = json_decode(file_get_contents('php://input'), true);
                $result = $employee->update($id, $data);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Employee updated successfully'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update employee'
                    ]);
                }
                break;
                
            case 'DELETE':
                $result = $employee->delete($id);
                
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Employee deleted successfully'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to delete employee'
                    ]);
                }
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    } elseif ($uri === '/api/employees/stats' || $uri === 'api/employees/stats') {
        if ($method === 'GET') {
            $employee = new Employee();
            $stats = $employee->getStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal Server Error',
        'error' => $e->getMessage()
    ]);
}
?> 