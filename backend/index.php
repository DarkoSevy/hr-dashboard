<?php
// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// If the request is for the API, redirect to the API index
if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
    require __DIR__ . '/api/index.php';
    exit;
}

// Otherwise, serve the frontend
echo "Welcome to HR Dashboard API. Please use /api endpoint for API requests."; 