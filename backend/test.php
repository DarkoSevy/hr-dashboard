<?php
// Basic PHP test
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Check if we can connect to MySQL
try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    echo "MySQL connection successful!<br>";
} catch(PDOException $e) {
    echo "MySQL connection failed: " . $e->getMessage() . "<br>";
}

// Check if we can write to files
$test_file = __DIR__ . '/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "File writing successful!<br>";
    unlink($test_file); // Clean up
} else {
    echo "File writing failed!<br>";
}

// Display PHP configuration
echo "<h2>PHP Configuration:</h2>";
echo "<pre>";
print_r([
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => ini_get('error_reporting'),
    'include_path' => ini_get('include_path'),
    'extension_dir' => ini_get('extension_dir')
]);
echo "</pre>";
?> 