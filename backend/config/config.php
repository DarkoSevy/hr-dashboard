<?php

return [
    'database' => [
        'host' => getenv('DB_HOST') ?: 'mysql',
        'dbname' => getenv('DB_NAME') ?: 'hr_dashboard',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: 'root',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    ],
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'options' => [
            'prefix' => 'hr_dashboard:'
        ]
    ],
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'your_jwt_secret_key',
        'expire' => 3600 * 24 // 24 hours
    ],
    'cors' => [
        'allowed_origins' => ['http://localhost:5173'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'expose_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true
    ],
    'app' => [
        'env' => getenv('APP_ENV') ?: 'development',
        'debug' => getenv('APP_DEBUG') === 'true',
        'timezone' => 'UTC',
        'pagination' => [
            'per_page' => 10
        ]
    ]
]; 