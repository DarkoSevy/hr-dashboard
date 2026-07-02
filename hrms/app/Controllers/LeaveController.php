<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;

/**
 * Leave workflow: employee request → supervisor approval → HR approval
 * → notification + balance update.
 */
class LeaveController extends Controller
{
    public function index(): void
    {
        if (!Auth::can('leaves.view') && !Auth::can('leaves.request')) {
            Auth::require('leaves.view');
        }
        $params = [];
        $where = '1=1';
        if (!Auth::can('leaves.view')) {
            // Employees see only their own requests.
            $where = 'lr.employee_id = ?';
            $params[] = Auth::employeeId() ?? 0;
        }
        $leaves = Database::fetchAll(
            "SELECT lr.*, lt.name AS leave_type,
                    CONCAT(e.first_name,' ',e.last_name) AS employee_name, e.employee_no,
                    e.manager_id
             FROM leave_requests lr
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             JOIN employees e ON e.id = lr.employee_id
             WHERE {$where}
             ORDER BY lr.created_at DESC LIMIT 500", $params
        );
        $balances = Auth::employeeId() ? Database::fetchAll(
            'SELECT lb.*, lt.name AS leave_type FROM leave_balances lb
             JOIN leave_types lt ON lt.id = lb.leave_type_id
             WHERE lb.employee_id = ? AND lb.year = YEAR(CURDATE())',
            [Auth::employeeId()]
        ) : [];
        $this->view('leaves/index', [
            'leaves' => $leaves,
            'balances' => $balances,
            'canApproveSup' => Auth::can('leaves.approve_supervisor'),
            'canApproveHr'  => Auth::can('leaves.approve_hr'),
        ]);
    }

    public function create(): void
    {
        Auth::require('leaves.request');
        $this->view('leaves/form', [
            'types' => Database::fetchAll('SELECT * FROM leave_types WHERE is_active = 1'),
            'employees' => Auth::can('leaves.view') ? Database::fetchAll(
                "SELECT id, CONCAT(first_name,' ',last_name,' (',employee_no,')') AS name
                 FROM employees WHERE status='active' ORDER BY first_name") : [],
        ]);
    }

    public function store(): void
    {
        Auth::require('leaves.request');
        $employeeId = Auth::can('leaves.view') && !empty($_POST['employee_id'])
            ? (int) $_POST['employee_id']
            : Auth::employeeId();
        if (!$employeeId) {
            flash('error', 'Your account is not linked to an employee record.');
            redirect('leaves');
        }
        $typeId = (int) ($_POST['leave_type_id'] ?? 0);
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';
        if (!$typeId || !$start || !$end || $end < $start) {
            flash('error', 'Please provide a valid leave type and date range.');
            redirect('leaves/create');
        }

        // Auto-calculate working days (weekends + public holidays excluded).
        $holidays = array_column(
            Database::fetchAll('SELECT date FROM holidays WHERE date BETWEEN ? AND ?', [$start, $end]),
            'date'
        );
        $days = working_days($start, $end, $holidays);
        if ($days <= 0) {
            flash('error', 'The selected range contains no working days.');
            redirect('leaves/create');
        }

        // Balance check
        $balance = Database::fetch(
            'SELECT * FROM leave_balances WHERE employee_id=? AND leave_type_id=? AND year=YEAR(?)',
            [$employeeId, $typeId, $start]
        );
        if ($balance && ($balance['entitled'] + $balance['carried_over'] - $balance['used']) < $days) {
            flash('error', 'Insufficient leave balance for this request.');
            redirect('leaves/create');
        }

        try {
            $attachment = $this->storeUpload('attachment', 'leaves');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('leaves/create');
        }

        $id = Database::insert('leave_requests', [
            'employee_id'   => $employeeId,
            'leave_type_id' => $typeId,
            'start_date'    => $start,
            'end_date'      => $end,
            'days'          => $days,
            'reason'        => trim($_POST['reason'] ?? '') ?: null,
            'attachment_path' => $attachment,
        ]);
        Audit::log('create', 'leave_requests', (string) $id);

        // Notify the supervisor.
        $managerUser = Database::fetch(
            'SELECT u.id FROM users u JOIN employees e ON e.manager_id = u.employee_id
             WHERE e.id = ? LIMIT 1', [$employeeId]
        );
        if ($managerUser) {
            $name = Database::scalar(
                "SELECT CONCAT(first_name,' ',last_name) FROM employees WHERE id=?", [$employeeId]);
            Mailer::notify((int) $managerUser['id'], 'leave_request',
                'Leave request awaiting your approval',
                "{$name} requested {$days} day(s) of leave from {$start} to {$end}.",
                url('leaves'));
        }
        flash('success', "Leave request submitted ({$days} working days). Awaiting supervisor approval.");
        redirect('leaves');
    }

    public function approve(int $id): void
    {
        $this->decide($id, approve: true);
    }

    public function reject(int $id): void
    {
        $this->decide($id, approve: false);
    }

    public function cancel(int $id): void
    {
        $leave = Database::fetch('SELECT * FROM leave_requests WHERE id = ?', [$id]);
        if ($leave && (int) $leave['employee_id'] === Auth::employeeId()
            && in_array($leave['status'], ['pending_supervisor', 'pending_hr'], true)) {
            Database::update('leave_requests', ['status' => 'cancelled'], $id);
            Audit::log('update', 'leave_requests', (string) $id, ['status' => $leave['status']], ['status' => 'cancelled']);
            flash('success', 'Leave request cancelled.');
        }
        redirect('leaves');
    }

    private function decide(int $id, bool $approve): void
    {
        $leave = Database::fetch(
            'SELECT lr.*, e.email AS emp_email, CONCAT(e.first_name," ",e.last_name) AS emp_name
             FROM leave_requests lr JOIN employees e ON e.id = lr.employee_id
             WHERE lr.id = ?', [$id]
        );
        if (!$leave) {
            redirect('leaves');
        }
        $comment = trim($_POST['comment'] ?? '') ?: null;
        $old = ['status' => $leave['status']];

        if ($leave['status'] === 'pending_supervisor') {
            Auth::require('leaves.approve_supervisor');
            $data = [
                'status' => $approve ? 'pending_hr' : 'rejected',
                'supervisor_id' => Auth::id(),
                'supervisor_at' => date('Y-m-d H:i:s'),
                'supervisor_comment' => $comment,
            ];
        } elseif ($leave['status'] === 'pending_hr') {
            Auth::require('leaves.approve_hr');
            $data = [
                'status' => $approve ? 'approved' : 'rejected',
                'hr_id' => Auth::id(),
                'hr_at' => date('Y-m-d H:i:s'),
                'hr_comment' => $comment,
            ];
        } else {
            flash('error', 'This request has already been finalised.');
            redirect('leaves');
        }

        Database::update('leave_requests', $data, $id);
        Audit::log('update', 'leave_requests', (string) $id, $old, ['status' => $data['status']]);

        // Final approval: consume the balance and mark leave days in attendance.
        if ($data['status'] === 'approved') {
            Database::query(
                'UPDATE leave_balances SET used = used + ?
                 WHERE employee_id=? AND leave_type_id=? AND year=YEAR(?)',
                [$leave['days'], $leave['employee_id'], $leave['leave_type_id'], $leave['start_date']]
            );
        }

        // Notify the employee (system + email).
        $empUser = Database::fetch(
            'SELECT id FROM users WHERE employee_id = ?', [$leave['employee_id']]);
        $verdict = match ($data['status']) {
            'pending_hr' => 'approved by your supervisor and forwarded to HR',
            'approved'   => 'approved',
            default      => 'rejected',
        };
        if ($empUser) {
            Mailer::notify((int) $empUser['id'], 'leave_decision',
                'Leave request ' . ($data['status'] === 'rejected' ? 'rejected' : 'update'),
                "Your leave request ({$leave['start_date']} → {$leave['end_date']}) was {$verdict}."
                . ($comment ? " Comment: {$comment}" : ''),
                url('leaves'));
        }
        flash('success', "Request {$verdict}.");
        redirect('leaves');
    }
}
