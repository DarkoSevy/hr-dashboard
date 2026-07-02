<?php
/**
 * Router for PHP's built-in dev server (Apache uses .htaccess instead):
 *   cd hrms && php -S 0.0.0.0:8080 -t public public/router.php
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false; // serve static assets directly
}
require __DIR__ . '/index.php';
