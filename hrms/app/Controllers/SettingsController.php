<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/** System settings editor (settings table key/value pairs). */
class SettingsController extends Controller
{
    /** Editable settings with labels and hints; unknown keys stay untouched. */
    private const KNOWN = [
        'company_name'  => ['Company Name', 'Shown in emails and payslips'],
        'company_email' => ['Company HR Email', 'Default sender / contact address'],
        'password_expiry_days' => ['Password Expiry (days)', '0 disables expiry'],
        'session_timeout_minutes' => ['Session Timeout (minutes)', 'Idle time before re-login'],
        'alert_days_before_expiry' => ['Expiry Alert Window (days)', 'How early the notifier warns about contracts/licenses/documents'],
        'rssb_employee_rate' => ['RSSB Employee Rate', 'Decimal fraction, e.g. 0.06 = 6%'],
        'rssb_employer_rate' => ['RSSB Employer Rate', 'Decimal fraction, e.g. 0.08 = 8%'],
    ];

    public function index(): void
    {
        Auth::require('settings.manage');
        $stored = array_column(
            Database::fetchAll('SELECT `key`, `value` FROM settings'), 'value', 'key'
        );
        $this->view('settings/index', ['known' => self::KNOWN, 'stored' => $stored]);
    }

    public function store(): void
    {
        Auth::require('settings.manage');
        $old = array_column(Database::fetchAll('SELECT `key`, `value` FROM settings'), 'value', 'key');
        $changed = [];
        foreach (array_keys(self::KNOWN) as $key) {
            if (!array_key_exists($key, $_POST)) {
                continue;
            }
            $value = trim((string) $_POST[$key]);
            if (($old[$key] ?? null) === $value) {
                continue;
            }
            Database::query(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$key, $value]
            );
            $changed[$key] = $value;
        }
        if ($changed) {
            Audit::log('update', 'settings', null,
                array_intersect_key($old, $changed), $changed);
            flash('success', 'Settings saved.');
        } else {
            flash('success', 'No changes to save.');
        }
        redirect('settings');
    }
}
