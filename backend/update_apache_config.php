<?php
$httpd_conf = 'C:/xampp/apache/conf/httpd.conf';
$php_config = [
    'LoadModule php_module "C:/xampp/php/php8apache2_4.dll"',
    'AddHandler application/x-httpd-php .php',
    'PHPIniDir "C:/xampp/php"'
];

if (file_exists($httpd_conf)) {
    $config = file_get_contents($httpd_conf);
    
    // Check if PHP module is already loaded
    if (strpos($config, 'LoadModule php_module') === false) {
        // Find the LoadModule section
        $pos = strpos($config, '#LoadModule');
        if ($pos !== false) {
            // Add PHP configuration after the last LoadModule
            $config = substr_replace($config, "\n" . implode("\n", $php_config) . "\n", $pos, 0);
            
            // Save the updated configuration
            if (file_put_contents($httpd_conf, $config)) {
                echo "PHP configuration has been added to httpd.conf\n";
                echo "Please restart Apache for changes to take effect.\n";
            } else {
                echo "Error: Could not write to httpd.conf. Please check file permissions.\n";
            }
        } else {
            echo "Error: Could not find LoadModule section in httpd.conf\n";
        }
    } else {
        echo "PHP module is already configured in httpd.conf\n";
    }
} else {
    echo "Error: httpd.conf not found at $httpd_conf\n";
}

// Verify the changes
if (file_exists($httpd_conf)) {
    $config = file_get_contents($httpd_conf);
    echo "\nVerifying configuration:\n";
    foreach ($php_config as $line) {
        echo "- " . (strpos($config, $line) !== false ? "Found" : "Not found") . ": $line\n";
    }
}
?> 