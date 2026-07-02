<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Self-service account settings: change password, update own contact
 * information, toggle email 2FA, and view/print own payslips.
 * Available to every authenticated user — no extra permission needed.
 */
class AccountController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $employee = Auth::employeeId()
            ? Database::fetch('SELECT * FROM employees WHERE id = ?', [Auth::employeeId()])
            : null;
        $this->view('account/index', ['user' => $user, 'employee' => $employee]);
    }

    public function password(): void
    {
        $user = Auth::user();
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
        } elseif ($new !== $confirm) {
            flash('error', 'New password and confirmation do not match.');
        } elseif (!Auth::validPassword($new)) {
            flash('error', 'Password must be 8+ characters with upper, lower, digit and symbol.');
        } else {
            Database::update('users', [
                'password_hash' => password_hash($new, PASSWORD_BCRYPT),
                'password_changed_at' => date('Y-m-d H:i:s'),
            ], (int) $user['id']);
            Audit::log('password_change', 'users', (string) $user['id']);
            flash('success', 'Password updated.');
        }
        redirect('account');
    }

    public function contact(): void
    {
        $empId = Auth::employeeId();
        if (!$empId) {
            flash('error', 'Your account is not linked to an employee record.');
            redirect('account');
        }
        $old = Database::fetch(
            'SELECT phone, email, address, emergency_name, emergency_phone, emergency_relation
             FROM employees WHERE id = ?', [$empId]
        );
        $data = $this->input(['phone', 'email', 'address', 'emergency_name', 'emergency_phone', 'emergency_relation']);
        if (isset($data['email']) && $data['email'] !== null
            && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please provide a valid email address.');
            redirect('account');
        }
        try {
            Database::update('employees', $data, $empId);
        } catch (\PDOException) {
            flash('error', 'That email address is already used by another employee.');
            redirect('account');
        }
        Audit::log('update', 'employees', (string) $empId, $old, $data);
        flash('success', 'Contact information updated.');
        redirect('account');
    }

    public function twoFactor(): void
    {
        $user = Auth::user();
        $enable = isset($_POST['enable']) ? 1 : 0;
        Database::update('users', ['two_factor_enabled' => $enable], (int) $user['id']);
        Audit::log('update', 'users', (string) $user['id'],
            ['two_factor_enabled' => $user['two_factor_enabled']],
            ['two_factor_enabled' => $enable]);
        flash('success', $enable
            ? 'Two-factor authentication enabled — you will receive a code by email at each sign-in.'
            : 'Two-factor authentication disabled.');
        redirect('account');
    }

    /** List own payslips from locked/exported payroll periods. */
    public function payslips(): void
    {
        $empId = Auth::employeeId();
        $entries = $empId ? Database::fetchAll(
            "SELECT pe.*, pp.name AS period_name, pp.start_date, pp.end_date, pp.status AS period_status
             FROM payroll_entries pe
             JOIN payroll_periods pp ON pp.id = pe.period_id
             WHERE pe.employee_id = ? AND pp.status IN ('locked','exported')
             ORDER BY pp.start_date DESC", [$empId]
        ) : [];
        $this->view('account/payslips', ['entries' => $entries, 'linked' => $empId !== null]);
    }

    /** Printable payslip (browser print → PDF). Own entries only. */
    public function payslip(int $id): void
    {
        $empId = Auth::employeeId();
        $entry = $empId ? Database::fetch(
            "SELECT pe.*, pp.name AS period_name, pp.start_date, pp.end_date,
                    e.employee_no, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                    e.rssb_number, e.tin_number, e.bank_name, e.bank_account,
                    d.name AS department, p.title AS position
             FROM payroll_entries pe
             JOIN payroll_periods pp ON pp.id = pe.period_id
             JOIN employees e ON e.id = pe.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN positions p ON p.id = e.position_id
             WHERE pe.id = ? AND pe.employee_id = ? AND pp.status IN ('locked','exported')",
            [$id, $empId]
        ) : null;
        if (!$entry) {
            flash('error', 'Payslip not found.');
            redirect('account/payslips');
        }
        \App\Core\View::render('account/payslip', ['entry' => $entry], bare: true);
    }
}
