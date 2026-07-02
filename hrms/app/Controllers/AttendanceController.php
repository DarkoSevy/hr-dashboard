<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/** Clock in/out with GPS capture, lateness detection and daily register. */
class AttendanceController extends Controller
{
    public function index(): void
    {
        Auth::require('attendance.clock');
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'] ?? '')
            ? $_GET['date'] : date('Y-m-d');

        $records = Auth::can('attendance.view') ? Database::fetchAll(
            "SELECT ar.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                    e.employee_no, d.name AS department
             FROM attendance_records ar
             JOIN employees e ON e.id = ar.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE ar.work_date = ?
             ORDER BY ar.clock_in", [$date]
        ) : [];

        $mine = Auth::employeeId() ? Database::fetch(
            'SELECT * FROM attendance_records WHERE employee_id = ? AND work_date = CURDATE()',
            [Auth::employeeId()]
        ) : null;

        $myHistory = Auth::employeeId() ? Database::fetchAll(
            'SELECT * FROM attendance_records WHERE employee_id = ?
             ORDER BY work_date DESC LIMIT 30', [Auth::employeeId()]
        ) : [];

        $this->view('attendance/index', [
            'records' => $records,
            'mine' => $mine,
            'myHistory' => $myHistory,
            'date' => $date,
            'canView' => Auth::can('attendance.view'),
        ]);
    }

    public function clockIn(): void
    {
        Auth::require('attendance.clock');
        $empId = Auth::employeeId();
        if (!$empId) {
            flash('error', 'Your account is not linked to an employee record.');
            redirect('attendance');
        }
        $existing = Database::fetch(
            'SELECT id FROM attendance_records WHERE employee_id = ? AND work_date = CURDATE()',
            [$empId]
        );
        if ($existing) {
            flash('error', 'You already clocked in today.');
            redirect('attendance');
        }

        // Lateness against the employee's active shift (default: Office Day).
        $shift = Database::fetch(
            'SELECT s.* FROM shift_assignments sa
             JOIN shifts s ON s.id = sa.shift_id
             WHERE sa.employee_id = ? AND sa.start_date <= CURDATE()
               AND (sa.end_date IS NULL OR sa.end_date >= CURDATE())
             ORDER BY sa.start_date DESC LIMIT 1', [$empId]
        ) ?? Database::fetch('SELECT * FROM shifts WHERE is_active = 1 ORDER BY id LIMIT 1');

        $late = 0;
        if ($shift) {
            $due = strtotime(date('Y-m-d ') . $shift['start_time']) + $shift['grace_minutes'] * 60;
            $late = max(0, (int) ceil((time() - $due) / 60));
        }

        $id = Database::insert('attendance_records', [
            'employee_id' => $empId,
            'work_date'   => date('Y-m-d'),
            'clock_in'    => date('Y-m-d H:i:s'),
            'in_latitude' => is_numeric($_POST['latitude'] ?? null) ? $_POST['latitude'] : null,
            'in_longitude'=> is_numeric($_POST['longitude'] ?? null) ? $_POST['longitude'] : null,
            'source'      => 'web',
            'status'      => $late > 0 ? 'late' : 'present',
            'late_minutes'=> $late,
        ]);
        Audit::log('create', 'attendance_records', (string) $id);
        flash('success', $late > 0 ? "Clocked in — marked late by {$late} min." : 'Clocked in. Have a great day!');
        redirect('attendance');
    }

    public function clockOut(): void
    {
        Auth::require('attendance.clock');
        $empId = Auth::employeeId();
        $record = $empId ? Database::fetch(
            'SELECT * FROM attendance_records WHERE employee_id = ? AND work_date = CURDATE()',
            [$empId]
        ) : null;
        if (!$record || $record['clock_out']) {
            flash('error', $record ? 'You already clocked out.' : 'Clock in first.');
            redirect('attendance');
        }

        $early = 0;
        $shift = Database::fetch(
            'SELECT s.* FROM shift_assignments sa JOIN shifts s ON s.id = sa.shift_id
             WHERE sa.employee_id = ? AND sa.start_date <= CURDATE()
               AND (sa.end_date IS NULL OR sa.end_date >= CURDATE())
             ORDER BY sa.start_date DESC LIMIT 1', [$empId]
        ) ?? Database::fetch('SELECT * FROM shifts WHERE is_active = 1 ORDER BY id LIMIT 1');
        if ($shift && $shift['end_time'] > $shift['start_time']) {
            $end = strtotime(date('Y-m-d ') . $shift['end_time']);
            $early = max(0, (int) floor(($end - time()) / 60));
        }

        Database::update('attendance_records', [
            'clock_out' => date('Y-m-d H:i:s'),
            'out_latitude' => is_numeric($_POST['latitude'] ?? null) ? $_POST['latitude'] : null,
            'out_longitude'=> is_numeric($_POST['longitude'] ?? null) ? $_POST['longitude'] : null,
            'early_departure_minutes' => $early,
        ], (int) $record['id']);
        Audit::log('update', 'attendance_records', (string) $record['id']);
        flash('success', 'Clocked out. See you tomorrow!');
        redirect('attendance');
    }
}
