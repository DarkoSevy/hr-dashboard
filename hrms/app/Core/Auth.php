<?php
declare(strict_types=1);

namespace App\Core;

/** Session authentication + role-based permission checks. */
class Auth
{
    private static ?array $permissionCache = null;

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        static $user = null;
        if ($user === null) {
            $user = Database::fetch(
                'SELECT u.*, r.name AS role_name, r.slug AS role_slug,
                        e.first_name, e.last_name, e.photo_path, e.id AS emp_id
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 LEFT JOIN employees e ON e.id = u.employee_id
                 WHERE u.id = ?',
                [self::id()]
            );
        }
        return $user;
    }

    public static function employeeId(): ?int
    {
        $u = self::user();
        return $u && $u['employee_id'] ? (int) $u['employee_id'] : null;
    }

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }
        if (self::$permissionCache === null) {
            self::$permissionCache = array_column(Database::fetchAll(
                'SELECT p.name FROM permissions p
                 JOIN role_permissions rp ON rp.permission_id = p.id
                 JOIN users u ON u.role_id = rp.role_id
                 WHERE u.id = ?',
                [self::id()]
            ), 'name');
        }
        return in_array($permission, self::$permissionCache, true);
    }

    /** Abort with 403 unless the user holds the permission. */
    public static function require(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);
            View::render('errors/403', ['permission' => $permission]);
            exit;
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        Database::query(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = ?, failed_attempts = 0 WHERE id = ?',
            [$_SERVER['REMOTE_ADDR'] ?? null, $user['id']]
        );
        Audit::log('login', 'users', (string) $user['id']);
    }

    public static function logout(): void
    {
        if (self::check()) {
            Audit::log('logout', 'users', (string) self::id());
        }
        session_unset();
        session_destroy();
    }

    /** Strong password policy: 8+ chars, upper, lower, digit, symbol. */
    public static function validPassword(string $password): bool
    {
        return (bool) preg_match(
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/',
            $password
        );
    }

    public static function passwordExpired(array $user): bool
    {
        // System Settings value wins, config is the fallback.
        $days = (int) ($GLOBALS['app_config']['app']['password_expiry_days'] ?? 90);
        try {
            $stored = Database::scalar("SELECT value FROM settings WHERE `key` = 'password_expiry_days'");
            if (is_numeric($stored)) {
                $days = (int) $stored;
            }
        } catch (\Throwable) {
        }
        if (!$user['password_changed_at'] || $days <= 0) {
            return false;
        }
        return strtotime($user['password_changed_at']) < strtotime("-{$days} days");
    }
}
