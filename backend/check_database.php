<?php
require_once 'config/database.php';

try {
    // Connect to database
    $database = new Database();
    $conn = $database->connect();
    
    // Check if database exists
    $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'hr_dashboard'");
    $dbExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dbExists) {
        echo "Database 'hr_dashboard' does not exist. Please create it first.\n";
        exit;
    }
    
    // Check if employees table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'employees'");
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tableExists) {
        echo "Table 'employees' does not exist. Please create it first.\n";
        exit;
    }
    
    // Check table structure
    $stmt = $conn->query("DESCRIBE employees");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Database and table check successful!\n";
    echo "Table structure:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check if there's any data
    $stmt = $conn->query("SELECT COUNT(*) as count FROM employees");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal records in employees table: {$count['count']}\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 