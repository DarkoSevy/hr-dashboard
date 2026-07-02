<?php
/**
 * PTS HRMS — front controller.
 * All web and API requests are routed through this file (see .htaccess).
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('STORAGE_PATH', BASE_PATH . '/storage');

require APP_PATH . '/Core/Helpers.php';

spl_autoload_register(function (string $class): void {
    $file = APP_PATH . '/' . str_replace(['App\\', '\\'], ['', '/'], $class) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use App\Core\App;

$config = require APP_PATH . '/Config/config.php';

error_reporting(E_ALL);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
date_default_timezone_set($config['app']['timezone']);

(new App($config))->run();
