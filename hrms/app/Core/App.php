<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router / kernel. URLs follow /module[/id][/action]:
 *   GET  /employees            → EmployeeController::index
 *   GET  /employees/create     → EmployeeController::create
 *   POST /employees            → EmployeeController::store
 *   GET  /employees/5          → EmployeeController::show(5)
 *   GET  /employees/5/edit     → EmployeeController::edit(5)
 *   POST /employees/5          → EmployeeController::update(5)
 *   POST /employees/5/delete   → EmployeeController::destroy(5)
 * /api/* is dispatched to ApiController and returns JSON.
 * Modules without a dedicated controller fall back to ResourceController,
 * driven by the metadata in Config/modules.php.
 */
class App
{
    public function __construct(private array $config)
    {
        Database::configure($config['db']);
        $GLOBALS['app_config'] = $config;
    }

    public function run(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        // REST API — token authenticated, JSON in/out.
        if (($segments[0] ?? '') === 'api') {
            (new \App\Controllers\ApiController())->dispatch(array_slice($segments, 1));
            return;
        }

        $this->startSession();

        $module = $segments[0] ?? 'dashboard';
        $isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

        // Public routes (no auth)
        if (in_array($module, ['login', 'logout', 'otp', 'forgot', 'reset'], true)) {
            $auth = new \App\Controllers\AuthController();
            match ($module) {
                'login'  => $isPost ? $auth->attempt() : $auth->loginForm(),
                'otp'    => $isPost ? $auth->verifyOtp() : $auth->otpForm(),
                'forgot' => $isPost ? $auth->sendReset() : $auth->forgotForm(),
                'reset'  => $isPost ? $auth->performReset() : $auth->resetForm(),
                'logout' => $auth->logout(),
            };
            return;
        }

        if (!Auth::check()) {
            redirect('login');
        }

        // Authenticated download of uploaded files (stored outside the web root).
        if ($module === 'storage') {
            (new \App\Controllers\StorageController())->stream(array_slice($segments, 1));
            return;
        }

        if ($isPost && !verify_csrf()) {
            http_response_code(419);
            exit('Invalid or expired form token. Go back and retry.');
        }

        [$id, $action] = $this->parseTail(array_slice($segments, 1));
        $controller = $this->resolveController($module);
        if ($controller === null) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $method = $this->resolveMethod($isPost, $id, $action);
        if (!method_exists($controller, $method)) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        $id !== null ? $controller->$method($id) : $controller->$method();
    }

    /** @return array{0:?int,1:?string} */
    private function parseTail(array $tail): array
    {
        $id = null;
        $action = null;
        foreach ($tail as $seg) {
            if (ctype_digit($seg)) {
                $id = (int) $seg;
            } else {
                $action = $seg;
            }
        }
        return [$id, $action];
    }

    private function resolveMethod(bool $isPost, ?int $id, ?string $action): string
    {
        if ($action !== null) {
            // create/edit/delete/approve/reject/clock-in/export...
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $action))));
            if ($isPost && $camel === 'delete') {
                return 'destroy';
            }
            return $camel;
        }
        if ($isPost) {
            return $id !== null ? 'update' : 'store';
        }
        return $id !== null ? 'show' : 'index';
    }

    private function resolveController(string $module): ?object
    {
        $studly = str_replace(' ', '', ucwords(str_replace('-', ' ', $module)));
        // Singularise common plural module names for controller lookup.
        $singular = preg_replace('/ies$/', 'y', $studly);
        $singular = preg_replace('/s$/', '', $singular);
        foreach ([$studly, $singular] as $name) {
            $class = "App\\Controllers\\{$name}Controller";
            if (class_exists($class)) {
                return new $class();
            }
        }
        // Generic metadata-driven module?
        $modules = require APP_PATH . '/Config/modules.php';
        if (isset($modules[$module])) {
            return new \App\Controllers\ResourceController($module, $modules[$module]);
        }
        return null;
    }

    private function startSession(): void
    {
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
        session_start();

        // Idle-session timeout: System Settings value wins, config is the fallback.
        $minutes = $this->config['app']['session_timeout_minutes'];
        try {
            $stored = Database::scalar("SELECT value FROM settings WHERE `key` = 'session_timeout_minutes'");
            if (is_numeric($stored) && (int) $stored > 0) {
                $minutes = (int) $stored;
            }
        } catch (\Throwable) {
            // DB unavailable — keep the configured default so login still renders.
        }
        $timeout = $minutes * 60;
        if (isset($_SESSION['_last_activity']) && time() - $_SESSION['_last_activity'] > $timeout) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = time();
    }
}
