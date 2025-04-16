<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the frontend URL from environment variable
$frontendUrl = getenv('FRONTEND_URL') ?: 'http://localhost:3000';

// Define allowed origins
$allowedOrigins = [
    $frontendUrl,
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'http://localhost:5173',  // Vite's default dev port
    'http://127.0.0.1:5173'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Set CORS headers
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
    header('Access-Control-Expose-Headers: Content-Length, X-JSON');
    header('Access-Control-Max-Age: 86400'); // 24 hours cache
}

// Always set JSON content type
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    }
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    
    http_response_code(204);
    exit();
}

try {
    // Get the request URI and remove query string
    $request_uri = $_SERVER['REQUEST_URI'];
    $base_path = '/api';
    $path = str_replace($base_path, '', $request_uri);
    $path = parse_url($path, PHP_URL_PATH);
    
    // Remove trailing slash if present
    $path = rtrim($path, '/');
    
    // Route the request
    switch ($path) {
        case '/employees':
        case 'employees': // Handle both with and without leading slash
            require_once __DIR__ . '/employees/index.php';
            break;
            
        case '/tasks':
        case 'tasks': // Handle both with and without leading slash
            require_once __DIR__ . '/tasks/index.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Endpoint not found',
                'path' => $path,
                'method' => $_SERVER['REQUEST_METHOD']
            ]);
            break;
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