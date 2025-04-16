<?php
// Check PHP version
echo "PHP Version: " . phpversion() . "\n\n";

// Check if required extensions are loaded
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli'];
echo "Checking required extensions:\n";
foreach ($required_extensions as $ext) {
    echo "- $ext: " . (extension_loaded($ext) ? "Loaded" : "Not loaded") . "\n";
}

// Check Apache error log
$error_log_path = 'C:/xampp/apache/logs/error.log';
echo "\nChecking Apache error log at: $error_log_path\n";

if (file_exists($error_log_path)) {
    $error_log = file_get_contents($error_log_path);
    echo "Last 10 lines of error log:\n";
    $lines = explode("\n", $error_log);
    $last_lines = array_slice($lines, -10);
    echo implode("\n", $last_lines);
} else {
    echo "Error log file not found!\n";
}

// Check Apache configuration
echo "\n\nChecking Apache configuration:\n";
$httpd_conf = 'C:/xampp/apache/conf/httpd.conf';
if (file_exists($httpd_conf)) {
    $config = file_get_contents($httpd_conf);
    echo "PHP module loaded: " . (strpos($config, 'LoadModule php_module') !== false ? "Yes" : "No") . "\n";
    echo "PHP handler configured: " . (strpos($config, 'AddHandler application/x-httpd-php') !== false ? "Yes" : "No") . "\n";
} else {
    echo "Apache configuration file not found!\n";
}
?> 