<?php
/** Global helper functions available everywhere. */

function env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value === false || $value === null) ? $default : (string) $value;
}

/** HTML-escape for safe output in views. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Build an application URL: url('employees/5') → /employees/5 */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/** Flash messages survive exactly one redirect. */
function flash(string $type, ?string $message = null): ?array
{
    if ($message !== null) {
        $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
        return null;
    }
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $flash;
}

/** CSRF token management. */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    return hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'] ?? '');
}

function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Working days between two dates, excluding weekends and given holidays. */
function working_days(string $start, string $end, array $holidays = []): float
{
    $begin = new DateTime($start);
    $finish = (new DateTime($end))->modify('+1 day');
    $days = 0;
    foreach (new DatePeriod($begin, new DateInterval('P1D'), $finish) as $day) {
        $isWeekend = (int) $day->format('N') >= 6;
        $isHoliday = in_array($day->format('Y-m-d'), $holidays, true);
        if (!$isWeekend && !$isHoliday) {
            $days++;
        }
    }
    return (float) $days;
}

/** Format a number as Rwandan Francs. */
function rwf(float|int|string|null $amount): string
{
    return number_format((float) $amount, 0) . ' RWF';
}

/** Human-friendly label from a snake_case / enum value. */
function label(?string $value): string
{
    return $value === null ? '' : ucwords(str_replace('_', ' ', $value));
}
