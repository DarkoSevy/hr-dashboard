<?php
/**
 * PTS HRMS configuration. Values may be overridden with environment variables
 * (e.g. via Apache SetEnv, docker-compose, or a .env loader).
 */
return [
    'app' => [
        'name'     => env('APP_NAME', 'PTS HRMS'),
        'url'      => env('APP_URL', 'http://localhost:8080'),
        'debug'    => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOL),
        'timezone' => env('APP_TIMEZONE', 'Africa/Kigali'),
        'session_timeout_minutes' => (int) env('SESSION_TIMEOUT', '30'),
        'password_expiry_days'    => (int) env('PASSWORD_EXPIRY_DAYS', '90'),
    ],
    'db' => [
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => env('DB_PORT', '3306'),
        'database' => env('DB_NAME', 'pts_hrms'),
        'username' => env('DB_USER', 'root'),
        'password' => env('DB_PASS', ''),
        'charset'  => 'utf8mb4',
    ],
    'mail' => [
        'enabled'    => filter_var(env('MAIL_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'host'       => env('MAIL_HOST', 'smtp.gmail.com'),
        'port'       => (int) env('MAIL_PORT', '587'),
        'username'   => env('MAIL_USERNAME', ''),
        'password'   => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_email' => env('MAIL_FROM', 'hr@pts.rw'),
        'from_name'  => env('MAIL_FROM_NAME', 'PTS Human Resources'),
    ],
    'uploads' => [
        'path'          => STORAGE_PATH . '/uploads',
        'max_size'      => 5 * 1024 * 1024, // 5 MB
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx'],
    ],
];
