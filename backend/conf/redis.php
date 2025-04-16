<?php
return [
    'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
    'port' => $_ENV['REDIS_PORT'] ?? 6379,
    'timeout' => 2.5,
];
