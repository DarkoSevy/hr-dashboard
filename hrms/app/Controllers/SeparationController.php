<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class SeparationController extends Controller
{
    private const STATUS_MAP = [
        'resignation'  => 'resigned',
        'retirement'   => 'retired',
        'dismissal'    => 'terminated',
        'redundancy'   => 'terminated',
        'contract_end' => 'terminated',
        'deceased'     => 'terminated',
        'other'        => 'terminated',
    ];

    public function index(): void
    {
        Auth::require('employees.view');
        $separations = Database::fetchAll(
            "SELECT s.*,
                    CONCAT(e.first_name,' ',e.last_name) AS employee_name, e.employee_no,
                    d.name AS department, p.title AS position
             FROM separations s
             JOIN employees e ON e.id = s.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN positions p ON p.id = e.position_id
             ORDER BY s.effective_date DESC"
        );
        $this->view('separations/index', ['separations' => $separations]);
    }

    public function create(): void
    {
        Auth::require('employees.manage');
        $employeeId = (int) ($_GET['employee_id'] ?? 0);
        $prefill = $employeeId
            ? Database::fetch('SELECT * FROM employees WHERE id = ?', [$employeeId])
            : null;
        $this->view('separations/form', [
            'separation' => null,
            'prefill'    => $prefill,
            'employees'  => $this->activeEmployees(),
        ]);
    }

    public function store(): void
    {
        Auth::require('employees.manage');
        $data = $this->collectData();
        if ($data === null) {
            return;
        }

        $id = Database::insert('separations', $data);

        // Update the employee status
        $newStatus = self::STATUS_MAP[$data['separation_type']] ?? 'terminated';
        Database::update('employees', [
            'status'      => $newStatus,
            'status_date' => $data['effective_date'],
        ], (int) $data['employee_id']);

        Audit::log('create', 'separations', (string) $id, null, $data);
        flash('success', 'Separation recorded. Employee status updated to ' . ucfirst($newStatus) . '.');
        redirect('separations/' . $id);
    }

    public function show(int $id): void
    {
        Auth::require('employees.view');
        $separation = $this->fetchOne($id);
        if (!$separation) {
            redirect('separations');
        }
        $this->view('separations/show', ['separation' => $separation]);
    }

    public function edit(int $id): void
    {
        Auth::require('employees.manage');
        $separation = $this->fetchOne($id);
        if (!$separation) {
            redirect('separations');
        }
        $this->view('separations/form', [
            'separation' => $separation,
            'prefill'    => null,
            'employees'  => Database::fetchAll(
                "SELECT id, CONCAT(first_name,' ',last_name,' (',employee_no,')') AS name
                 FROM employees ORDER BY first_name"
            ),
        ]);
    }

    public function update(int $id): void
    {
        Auth::require('employees.manage');
        $old = Database::fetch('SELECT * FROM separations WHERE id = ?', [$id]);
        if (!$old) {
            redirect('separations');
        }
        $data = $this->collectData();
        if ($data === null) {
            return;
        }
        unset($data['employee_id'], $data['created_by']); // immutable after creation
        Database::update('separations', $data, $id);
        Audit::log('update', 'separations', (string) $id, $old, $data);
        flash('success', 'Separation record updated.');
        redirect('separations/' . $id);
    }

    public function destroy(int $id): void
    {
        Auth::require('employees.manage');
        $old = Database::fetch('SELECT * FROM separations WHERE id = ?', [$id]);
        if ($old) {
            Database::delete('separations', $id);
            Audit::log('delete', 'separations', (string) $id, $old);
            flash('success', 'Separation record deleted.');
        }
        redirect('separations');
    }

    // -------------------------------------------------------------------------

    private function fetchOne(int $id): ?array
    {
        return Database::fetch(
            "SELECT s.*,
                    CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                    e.employee_no, e.id AS emp_id,
                    d.name AS department, p.title AS position
             FROM separations s
             JOIN employees e ON e.id = s.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN positions p ON p.id = e.position_id
             WHERE s.id = ?", [$id]
        );
    }

    private function collectData(): ?array
    {
        $empId = (int) ($_POST['employee_id'] ?? 0);
        $type  = trim($_POST['separation_type'] ?? '');
        $date  = trim($_POST['effective_date'] ?? '');

        if (!$empId || !$type || !$date) {
            flash('error', 'Employee, separation type and effective date are required.');
            redirect('separations/create');
            return null;
        }

        return [
            'employee_id'          => $empId,
            'separation_type'      => $type,
            'effective_date'       => $date,
            'notice_date'          => ($_POST['notice_date'] ?: null),
            'reason'               => trim($_POST['reason'] ?? '') ?: null,
            'exit_interview_date'  => ($_POST['exit_interview_date'] ?: null),
            'exit_interview_notes' => trim($_POST['exit_interview_notes'] ?? '') ?: null,
            'clearance_status'     => $_POST['clearance_status'] ?? 'pending',
            'final_pay_amount'     => ($_POST['final_pay_amount'] !== '' ? (float) $_POST['final_pay_amount'] : null),
            'final_pay_date'       => ($_POST['final_pay_date'] ?: null),
            'rehire_eligible'      => isset($_POST['rehire_eligible']) ? 1 : 0,
            'notes'                => trim($_POST['notes'] ?? '') ?: null,
            'created_by'           => Auth::id(),
        ];
    }

    private function activeEmployees(): array
    {
        return Database::fetchAll(
            "SELECT id, CONCAT(first_name,' ',last_name,' (',employee_no,')') AS name
             FROM employees WHERE status='active' ORDER BY first_name"
        );
    }
}
