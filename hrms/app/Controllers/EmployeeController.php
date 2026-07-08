<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class EmployeeController extends Controller
{
    /** Columns editable through the employee form. */
    private const FIELDS = [
        'first_name', 'last_name', 'national_id', 'passport_no', 'gender',
        'date_of_birth', 'marital_status', 'nationality', 'blood_group',
        'phone', 'email', 'emergency_name', 'emergency_phone', 'emergency_relation',
        'address', 'department_id', 'position_id', 'branch_id', 'manager_id',
        'employment_type', 'date_hired', 'probation_end', 'confirmation_date',
        'status', 'basic_salary', 'bank_name', 'bank_account', 'tin_number',
        'rssb_number', 'medical_insurer', 'medical_policy_no', 'medical_expiry',
        'is_driver',
    ];

    public function index(): void
    {
        Auth::require('employees.view');
        $employees = Database::fetchAll(
            "SELECT e.id, e.employee_no, e.photo_path,
                    CONCAT(e.first_name,' ',e.last_name) AS full_name,
                    e.phone, e.email, e.employment_type, e.status, e.date_hired, e.is_driver,
                    d.name AS department, p.title AS position
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN positions p ON p.id = e.position_id
             ORDER BY e.last_name, e.first_name"
        );
        $this->view('employees/index', ['employees' => $employees]);
    }

    public function create(): void
    {
        Auth::require('employees.manage');
        $this->view('employees/form', ['employee' => null] + $this->lookups());
    }

    public function edit(int $id): void
    {
        Auth::require('employees.manage');
        $employee = Database::fetch('SELECT * FROM employees WHERE id = ?', [$id]);
        if (!$employee) {
            redirect('employees');
        }
        $this->view('employees/form', ['employee' => $employee] + $this->lookups());
    }

    public function show(int $id): void
    {
        Auth::require('employees.view');
        $employee = Database::fetch(
            "SELECT e.*, d.name AS department, p.title AS position, b.name AS branch,
                    CONCAT(m.first_name,' ',m.last_name) AS manager_name
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN positions p ON p.id = e.position_id
             LEFT JOIN branches b ON b.id = e.branch_id
             LEFT JOIN employees m ON m.id = e.manager_id
             WHERE e.id = ?", [$id]
        );
        if (!$employee) {
            redirect('employees');
        }
        $this->view('employees/show', [
            'employee'  => $employee,
            'documents' => Database::fetchAll(
                'SELECT * FROM employee_documents WHERE employee_id = ? ORDER BY created_at DESC', [$id]),
            'contracts' => Database::fetchAll(
                'SELECT * FROM contracts WHERE employee_id = ? ORDER BY start_date DESC', [$id]),
            'leaves' => Database::fetchAll(
                'SELECT lr.*, lt.name AS leave_type FROM leave_requests lr
                 JOIN leave_types lt ON lt.id = lr.leave_type_id
                 WHERE lr.employee_id = ? ORDER BY lr.start_date DESC LIMIT 20', [$id]),
            'balances' => Database::fetchAll(
                'SELECT lb.*, lt.name AS leave_type FROM leave_balances lb
                 JOIN leave_types lt ON lt.id = lb.leave_type_id
                 WHERE lb.employee_id = ? AND lb.year = YEAR(CURDATE())', [$id]),
            'assets' => Database::fetchAll(
                'SELECT aa.*, a.asset_tag, a.name AS asset_name, a.category
                 FROM asset_assignments aa JOIN assets a ON a.id = aa.asset_id
                 WHERE aa.employee_id = ? ORDER BY aa.assigned_at DESC', [$id]),
            'reviews' => Database::fetchAll(
                'SELECT * FROM performance_reviews WHERE employee_id = ? ORDER BY created_at DESC', [$id]),
            'trainings' => Database::fetchAll(
                'SELECT tp.*, tc.name AS course, ts.start_date
                 FROM training_participants tp
                 JOIN training_sessions ts ON ts.id = tp.session_id
                 JOIN training_courses tc ON tc.id = ts.course_id
                 WHERE tp.employee_id = ? ORDER BY ts.start_date DESC', [$id]),
            'discipline' => Database::fetchAll(
                'SELECT * FROM disciplinary_cases WHERE employee_id = ? ORDER BY case_date DESC', [$id]),
            'driver'     => Database::fetch('SELECT * FROM drivers WHERE employee_id = ?', [$id]),
            'promotions' => Database::fetchAll(
                "SELECT pr.*, op.title AS old_position, np.title AS new_position
                 FROM promotions pr
                 LEFT JOIN positions op ON op.id = pr.old_position_id
                 JOIN positions np ON np.id = pr.new_position_id
                 WHERE pr.employee_id = ? ORDER BY pr.effective_date DESC", [$id]),
            'separation' => Database::fetch(
                'SELECT * FROM separations WHERE employee_id = ? ORDER BY effective_date DESC LIMIT 1', [$id]),
        ]);
    }

    public function store(): void
    {
        Auth::require('employees.manage');
        $data = $this->collect();
        $data['employee_no'] = $this->nextEmployeeNo();
        $data['created_by'] = Auth::id();
        try {
            if ($photo = $this->storeUpload('photo', 'photos')) {
                $data['photo_path'] = $photo;
            }
            $id = Database::insert('employees', $data);
        } catch (\Throwable $e) {
            flash('error', 'Could not save employee: ' . $e->getMessage());
            redirect('employees/create');
        }
        // Open the current-year leave balances for the new employee.
        Database::query(
            "INSERT INTO leave_balances (employee_id, leave_type_id, year, entitled)
             SELECT ?, id, YEAR(CURDATE()), days_per_year FROM leave_types
             WHERE is_active = 1 AND (gender = 'all' OR gender = ?)",
            [$id, $data['gender']]
        );
        Audit::log('create', 'employees', (string) $id, null, $data);
        flash('success', 'Employee ' . $data['employee_no'] . ' created.');
        redirect('employees/' . $id);
    }

    public function update(int $id): void
    {
        Auth::require('employees.manage');
        $old = Database::fetch('SELECT * FROM employees WHERE id = ?', [$id]);
        if (!$old) {
            redirect('employees');
        }
        $data = $this->collect();
        try {
            if ($photo = $this->storeUpload('photo', 'photos')) {
                $data['photo_path'] = $photo;
            }
            Database::update('employees', $data, $id);
        } catch (\Throwable $e) {
            flash('error', 'Could not update employee: ' . $e->getMessage());
            redirect("employees/{$id}/edit");
        }
        Audit::log('update', 'employees', (string) $id,
            array_intersect_key($old, $data), $data);
        flash('success', 'Employee updated.');
        redirect('employees/' . $id);
    }

    public function destroy(int $id): void
    {
        Auth::require('employees.manage');
        $old = Database::fetch('SELECT * FROM employees WHERE id = ?', [$id]);
        if ($old) {
            Database::delete('employees', $id);
            Audit::log('delete', 'employees', (string) $id, $old);
            flash('success', 'Employee record deleted.');
        }
        redirect('employees');
    }

    /** Upload a document into the employee's vault (versioned). */
    public function uploadDocument(int $id): void
    {
        Auth::require('documents.manage');
        try {
            $path = $this->storeUpload('file', 'documents');
            if (!$path) {
                throw new \RuntimeException('Choose a file to upload.');
            }
            $docType = $_POST['doc_type'] ?? 'other';
            $previous = Database::fetch(
                'SELECT id, version FROM employee_documents
                 WHERE employee_id = ? AND doc_type = ? ORDER BY version DESC LIMIT 1',
                [$id, $docType]
            );
            $docId = Database::insert('employee_documents', [
                'employee_id' => $id,
                'doc_type'    => $docType,
                'title'       => trim($_POST['title'] ?? '') ?: label($docType),
                'file_path'   => $path,
                'version'     => $previous ? $previous['version'] + 1 : 1,
                'replaces_id' => $previous['id'] ?? null,
                'expiry_date' => ($_POST['expiry_date'] ?? '') ?: null,
                'uploaded_by' => Auth::id(),
            ]);
            Audit::log('create', 'employee_documents', (string) $docId);
            flash('success', 'Document uploaded.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect("employees/{$id}");
    }

    // -------------------------------------------------------------------------

    private function collect(): array
    {
        $data = $this->input(self::FIELDS);
        $data['is_driver'] = isset($_POST['is_driver']) ? 1 : 0;
        foreach (['first_name', 'last_name', 'gender', 'date_hired'] as $required) {
            if (empty($data[$required])) {
                flash('error', label($required) . ' is required.');
                redirect('employees/create');
            }
        }
        return $data;
    }

    private function nextEmployeeNo(): string
    {
        $max = (int) Database::scalar(
            "SELECT MAX(CAST(SUBSTRING(employee_no, 5) AS UNSIGNED)) FROM employees WHERE employee_no LIKE 'PTS-%'"
        );
        return sprintf('PTS-%04d', $max + 1);
    }

    private function lookups(): array
    {
        return [
            'departments' => Database::fetchAll('SELECT id, name FROM departments WHERE is_active=1 ORDER BY name'),
            'positions'   => Database::fetchAll('SELECT id, title, department_id FROM positions WHERE is_active=1 ORDER BY title'),
            'branches'    => Database::fetchAll('SELECT id, name FROM branches WHERE is_active=1 ORDER BY name'),
            'managers'    => Database::fetchAll(
                "SELECT id, CONCAT(first_name,' ',last_name,' (',employee_no,')') AS name
                 FROM employees WHERE status='active' ORDER BY first_name"),
        ];
    }
}
