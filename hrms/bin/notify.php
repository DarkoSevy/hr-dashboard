<?php
/**
 * PTS HRMS — scheduled notifier & housekeeping. Run daily via cron:
 *   0 6 * * * php /var/www/hrms/bin/notify.php >> /var/log/hrms-notify.log 2>&1
 *
 * Sends system + email notifications for:
 *   - contracts expiring (configurable window, default 30 days)
 *   - driver license / permit / medical exam expiry
 *   - employee medical insurance expiry
 *   - document vault expiry
 *   - training certificate expiry
 *   - probation periods ending (14 days)
 *   - birthdays (today)
 *   - upcoming training sessions (7 days)
 *   - RSSB declaration reminder (5th of the month)
 * Housekeeping:
 *   - marks yesterday's unexplained no-shows as absent
 *   - expires contracts past their end date
 * Each alert is deduplicated: the same user/type/title is not repeated
 * within 20 days.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

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

use App\Core\Database;
use App\Core\Mailer;

$config = require APP_PATH . '/Config/config.php';
date_default_timezone_set($config['app']['timezone']);
Database::configure($config['db']);
$GLOBALS['app_config'] = $config;

$window = (int) (Database::scalar("SELECT value FROM settings WHERE `key`='alert_days_before_expiry'") ?: 30);
$sent = 0;

/** HR staff + admins receive operational alerts. */
function hrUserIds(): array
{
    static $ids = null;
    if ($ids === null) {
        $ids = array_map('intval', array_column(Database::fetchAll(
            "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.is_active = 1 AND r.slug IN ('admin','hr_manager','hr_officer')"
        ), 'id'));
    }
    return $ids;
}

/** Notify a user once per 20 days for the same type+title. */
function notifyOnce(int $userId, string $type, string $title, string $body, ?string $link = null): bool
{
    $dup = Database::scalar(
        'SELECT COUNT(*) FROM notifications
         WHERE user_id = ? AND type = ? AND title = ?
           AND created_at > DATE_SUB(NOW(), INTERVAL 20 DAY)',
        [$userId, $type, $title]
    );
    if ($dup > 0) {
        return false;
    }
    Mailer::notify($userId, $type, $title, $body, $link);
    return true;
}

/** Fan an alert out to HR plus (optionally) the employee's own account. */
function alert(string $type, string $title, string $body, ?int $employeeId = null, ?string $link = null): int
{
    $count = 0;
    $targets = hrUserIds();
    if ($employeeId !== null) {
        $own = Database::scalar('SELECT id FROM users WHERE employee_id = ? AND is_active = 1', [$employeeId]);
        if ($own) {
            $targets[] = (int) $own;
        }
    }
    foreach (array_unique($targets) as $userId) {
        if (notifyOnce($userId, $type, $title, $body, $link)) {
            $count++;
        }
    }
    return $count;
}

// ---------------------------------------------------------------- expiries

$expiries = [
    ['contract_expiry', "SELECT c.employee_id, CONCAT(e.first_name,' ',e.last_name) AS name, c.end_date AS due
        FROM contracts c JOIN employees e ON e.id=c.employee_id
        WHERE c.status='active' AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Contract expiring', 'contract expires on'],
    ['license_expiry', "SELECT d.employee_id, CONCAT(e.first_name,' ',e.last_name) AS name, d.license_expiry AS due
        FROM drivers d JOIN employees e ON e.id=d.employee_id
        WHERE d.license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Driving license expiring', 'driving license expires on'],
    ['permit_expiry', "SELECT d.employee_id, CONCAT(e.first_name,' ',e.last_name) AS name, d.permit_expiry AS due
        FROM drivers d JOIN employees e ON e.id=d.employee_id
        WHERE d.permit_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Driver permit expiring', 'permit expires on'],
    ['medical_expiry', "SELECT d.employee_id, CONCAT(e.first_name,' ',e.last_name) AS name, d.medical_exam_expiry AS due
        FROM drivers d JOIN employees e ON e.id=d.employee_id
        WHERE d.medical_exam_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Driver medical exam expiring', 'medical examination expires on'],
    ['insurance_expiry', "SELECT e.id AS employee_id, CONCAT(e.first_name,' ',e.last_name) AS name, e.medical_expiry AS due
        FROM employees e
        WHERE e.status='active' AND e.medical_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Medical insurance expiring', 'medical insurance expires on'],
    ['document_expiry', "SELECT ed.employee_id, CONCAT(e.first_name,' ',e.last_name, ' (', ed.title, ')') AS name, ed.expiry_date AS due
        FROM employee_documents ed JOIN employees e ON e.id=ed.employee_id
        WHERE ed.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Document expiring', 'document expires on'],
    ['certificate_expiry', "SELECT tp.employee_id, CONCAT(e.first_name,' ',e.last_name, ' (', tc.name, ')') AS name, tp.certificate_expiry AS due
        FROM training_participants tp
        JOIN employees e ON e.id=tp.employee_id
        JOIN training_sessions ts ON ts.id=tp.session_id
        JOIN training_courses tc ON tc.id=ts.course_id
        WHERE tp.certificate_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$window} DAY)",
        'Training certificate expiring', 'training certificate expires on'],
];

foreach ($expiries as [$type, $sql, $title, $phrase]) {
    foreach (Database::fetchAll($sql) as $row) {
        $sent += alert($type, "{$title}: {$row['name']}",
            "{$row['name']} — {$phrase} {$row['due']}. Please arrange renewal.",
            (int) $row['employee_id'], url('reports'));
    }
}

// ---------------------------------------------------------------- probation & birthdays

foreach (Database::fetchAll(
    "SELECT id, CONCAT(first_name,' ',last_name) AS name, probation_end
     FROM employees WHERE status='active'
       AND probation_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)"
) as $row) {
    $sent += alert('probation_end', "Probation ending: {$row['name']}",
        "{$row['name']}'s probation ends on {$row['probation_end']}. Schedule the confirmation review.",
        (int) $row['id']);
}

foreach (Database::fetchAll(
    "SELECT id, CONCAT(first_name,' ',last_name) AS name
     FROM employees WHERE status='active'
       AND DATE_FORMAT(date_of_birth,'%m-%d') = DATE_FORMAT(CURDATE(),'%m-%d')"
) as $row) {
    $sent += alert('birthday', "Birthday today: {$row['name']}",
        "{$row['name']} celebrates a birthday today. 🎉", (int) $row['id']);
}

// ---------------------------------------------------------------- training due

foreach (Database::fetchAll(
    "SELECT tp.employee_id, tc.name AS course, ts.start_date, ts.location
     FROM training_participants tp
     JOIN training_sessions ts ON ts.id = tp.session_id
     JOIN training_courses tc ON tc.id = ts.course_id
     WHERE ts.status = 'planned'
       AND ts.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
) as $row) {
    $own = Database::scalar('SELECT id FROM users WHERE employee_id = ? AND is_active = 1', [$row['employee_id']]);
    if ($own && notifyOnce((int) $own, 'training_due',
        "Upcoming training: {$row['course']}",
        "Your training '{$row['course']}' starts on {$row['start_date']}"
        . ($row['location'] ? " at {$row['location']}" : '') . '.')) {
        $sent++;
    }
}

// ---------------------------------------------------------------- RSSB reminder (5th of month)

if ((int) date('j') === 5) {
    $financeUsers = Database::fetchAll(
        "SELECT u.id FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.is_active = 1 AND r.slug IN ('admin','hr_manager','finance_officer')"
    );
    foreach ($financeUsers as $u) {
        if (notifyOnce((int) $u['id'], 'rssb_reminder',
            'RSSB declaration reminder — ' . date('F Y', strtotime('last month')),
            'Monthly RSSB contributions for ' . date('F Y', strtotime('last month'))
            . ' are due by the 15th. Export the payroll and file the declaration.')) {
            $sent++;
        }
    }
}

// ---------------------------------------------------------------- housekeeping

// Expire contracts past their end date.
$expired = Database::query(
    "UPDATE contracts SET status='expired' WHERE status='active' AND end_date < CURDATE()"
)->rowCount();

// Mark yesterday's unexplained no-shows absent (working days only).
$yesterday = date('Y-m-d', strtotime('-1 day'));
$absent = 0;
$isWeekend = (int) date('N', strtotime($yesterday)) >= 6;
$isHoliday = (bool) Database::scalar('SELECT COUNT(*) FROM holidays WHERE date = ?', [$yesterday]);
if (!$isWeekend && !$isHoliday) {
    $absent = Database::query(
        "INSERT INTO attendance_records (employee_id, work_date, source, status)
         SELECT e.id, ?, 'manual', 'absent'
         FROM employees e
         WHERE e.status = 'active'
           AND NOT EXISTS (SELECT 1 FROM attendance_records ar
                           WHERE ar.employee_id = e.id AND ar.work_date = ?)
           AND NOT EXISTS (SELECT 1 FROM leave_requests lr
                           WHERE lr.employee_id = e.id AND lr.status = 'approved'
                             AND ? BETWEEN lr.start_date AND lr.end_date)",
        [$yesterday, $yesterday, $yesterday]
    )->rowCount();
}

echo sprintf(
    "[%s] notify.php done — %d notification(s) sent, %d contract(s) expired, %d absence(s) recorded for %s\n",
    date('Y-m-d H:i:s'), $sent, $expired, $absent, $yesterday
);
