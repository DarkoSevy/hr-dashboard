<?php
include_once 'config/database.php';

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();

    // Read and execute the database schema
    $schema_sql = file_get_contents('database.sql');
    $conn->exec($schema_sql);
    echo "Database schema created successfully.\n";

    // Clear existing data in reverse order of foreign key dependencies
    $clear_data_sql = "
        SET FOREIGN_KEY_CHECKS=0;
        TRUNCATE TABLE tasks;
        TRUNCATE TABLE employees;
        TRUNCATE TABLE positions;
        TRUNCATE TABLE departments;
        SET FOREIGN_KEY_CHECKS=1;
    ";
    $conn->exec($clear_data_sql);
    echo "Existing data cleared successfully.\n";

    // Read and execute the sample data
    $sample_data_sql = file_get_contents('sample_data.sql');
    $conn->exec($sample_data_sql);
    echo "Sample data imported successfully.\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 