<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class PromotionController extends Controller
{
    public function index(): void
    {
        Auth::require('employees.view');
        $promotions = Database::fetchAll(
            "SELECT pr.*,
                    CONCAT(e.first_name,' ',e.last_name) AS employee_name, e.employee_no,
                    op.title AS old_position, np.title AS new_position,
                    od.name AS old_department, nd.name AS new_department,
                    CONCAT(ap.first_name,' ',ap.last_name) AS approved_by_name
             FROM promotions pr
             JOIN employees e ON e.id = pr.employee_id
             LEFT JOIN positions op ON op.id = pr.old_position_id
             JOIN positions np ON np.id = pr.new_position_id
             LEFT JOIN departments od ON od.id = pr.old_department_id
             LEFT JOIN departments nd ON nd.id = pr.new_department_id
             LEFT JOIN employees ap ON ap.id = pr.approved_by
             ORDER BY pr.effective_date DESC"
        );
        $this->view('promotions/index', ['promotions' => $promotions]);
    }

    public function create(): void
    {
        Auth::require('employees.manage');
        $employeeId = (int) ($_GET['employee_id'] ?? 0);
        $prefill = $employeeId ? Database::fetch('SELECT * FROM employees WHERE id = ?', [$employeeId]) : null;
        $this->view('promotions/form', ['promotion' => null, 'prefill' => $prefill] + $this->lookups());
    }

    public function store(): void
    {
        Auth::require('employees.manage');
        $data = $this->collectData();
        if ($data === null) {
            return;
        }

        $id = Database::insert('promotions', $data);

        // Apply changes to the employee record
        $update = ['position_id' => $data['new_position_id']];
        if (!empty($data['new_department_id'])) {
            $update['department_id'] = $data['new_department_id'];
        }
        if ($data['new_salary'] !== null) {
            $update['basic_salary'] = $data['new_salary'];
        }
        Database::update('employees', $update, (int) $data['employee_id']);

        Audit::log('create', 'promotions', (string) $id, null, $data);
        flash('success', 'Promotion recorded and employee profile updated.');
        redirect('promotions/' . $id);
    }

    public function show(int $id): void
    {
        Auth::require('employees.view');
        $promotion = $this->fetchOne($id);
        if (!$promotion) {
            redirect('promotions');
        }
        $this->view('promotions/show', ['promotion' => $promotion]);
    }

    public function edit(int $id): void
    {
        Auth::require('employees.manage');
        $promotion = $this->fetchOne($id);
        if (!$promotion) {
            redirect('promotions');
        }
        $this->view('promotions/form', ['promotion' => $promotion, 'prefill' => null] + $this->lookups());
    }

    public function update(int $id): void
    {
        Auth::require('employees.manage');
        $old = Database::fetch('SELECT * FROM promotions WHERE id = ?', [$id]);
        if (!$old) {
            redirect('promotions');
        }
        $data = $this->collectData();
        if ($data === null) {
            return;
        }
        Database::update('promotions', $data, $id);
        Audit::log('update', 'promotions', (string) $id, $old, $data);
        flash('success', 'Promotion record updated.');
        redirect('promotions/' . $id);
    }

    public function destroy(int $id): void
    {
        Auth::require('employees.manage');
        $old = Database::fetch('SELECT * FROM promotions WHERE id = ?', [$id]);
        if ($old) {
            Database::delete('promotions', $id);
            Audit::log('delete', 'promotions', (string) $id, $old);
            flash('success', 'Promotion record deleted.');
        }
        redirect('promotions');
    }

    // -------------------------------------------------------------------------

    private function fetchOne(int $id): ?array
    {
        return Database::fetch(
            "SELECT pr.*,
                    CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                    e.employee_no, e.id AS emp_id,
                    op.title AS old_position, np.title AS new_position,
                    od.name AS old_department, nd.name AS new_department,
                    CONCAT(ap.first_name,' ',ap.last_name) AS approved_by_name
             FROM promotions pr
             JOIN employees e ON e.id = pr.employee_id
             LEFT JOIN positions op ON op.id = pr.old_position_id
             JOIN positions np ON np.id = pr.new_position_id
             LEFT JOIN departments od ON od.id = pr.old_department_id
             LEFT JOIN departments nd ON nd.id = pr.new_department_id
             LEFT JOIN employees ap ON ap.id = pr.approved_by
             WHERE pr.id = ?", [$id]
        );
    }

    private function collectData(): ?array
    {
        $empId    = (int) ($_POST['employee_id'] ?? 0);
        $newPosId = (int) ($_POST['new_position_id'] ?? 0);
        $date     = trim($_POST['effective_date'] ?? '');

        if (!$empId || !$newPosId || !$date) {
            flash('error', 'Employee, new position and effective date are required.');
            redirect('promotions/create');
            return null;
        }

        $employee = Database::fetch(
            'SELECT position_id, department_id, basic_salary FROM employees WHERE id = ?', [$empId]
        );

        return [
            'employee_id'       => $empId,
            'effective_date'    => $date,
            'promotion_type'    => $_POST['promotion_type'] ?? 'promotion',
            'old_position_id'   => ($_POST['old_position_id'] ?: null) ?? ($employee['position_id'] ?? null),
            'new_position_id'   => $newPosId,
            'old_department_id' => ($_POST['old_department_id'] ?: null) ?? ($employee['department_id'] ?? null),
            'new_department_id' => ($_POST['new_department_id'] ?: null),
            'old_salary'        => ($_POST['old_salary'] !== '' ? (float) $_POST['old_salary'] : null)
                                    ?? ($employee['basic_salary'] ?? null),
            'new_salary'        => ($_POST['new_salary'] !== '' ? (float) $_POST['new_salary'] : null),
            'reason'            => trim($_POST['reason'] ?? '') ?: null,
            'approved_by'       => ($_POST['approved_by'] ?: null),
            'created_by'        => Auth::id(),
        ];
    }

    private function lookups(): array
    {
        return [
            'employees'   => Database::fetchAll(
                "SELECT id, CONCAT(first_name,' ',last_name,' (',employee_no,')') AS name
                 FROM employees WHERE status='active' ORDER BY first_name"
            ),
            'positions'   => Database::fetchAll(
                'SELECT id, title, department_id FROM positions WHERE is_active=1 ORDER BY title'
            ),
            'departments' => Database::fetchAll(
                'SELECT id, name FROM departments WHERE is_active=1 ORDER BY name'
            ),
            'managers'    => Database::fetchAll(
                "SELECT id, CONCAT(first_name,' ',last_name,' (',employee_no,')') AS name
                 FROM employees WHERE status='active' ORDER BY first_name"
            ),
        ];
    }
}
