<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/utils/RedisClient.php';

try {
    $redis = RedisClient::getInstance();
    echo "Redis connection successful!\n";
    
    // Test set and get operations
    $redis->set('test_key', 'test_value');
    $value = $redis->get('test_key');
    echo "Test value retrieved: " . $value . "\n";
    
} catch (Exception $e) {
    echo "Redis connection error: " . $e->getMessage() . "\n";
}
?> 