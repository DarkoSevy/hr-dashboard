<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Database;

/**
 * Applicants: generic CRUD (via ResourceController) plus one-click
 * conversion of a hired applicant into an employee record.
 */
class ApplicantController extends ResourceController
{
    public function __construct()
    {
        $modules = require APP_PATH . '/Config/modules.php';
        parent::__construct('applicants', $modules['applicants']);
    }

    /** POST /applicants/{id}/convert — create the employee from the applicant. */
    public function convert(int $id): void
    {
        Auth::require('employees.manage');
        $applicant = Database::fetch(
            'SELECT a.*, v.department_id, v.position_id, v.employment_type
             FROM applicants a JOIN job_vacancies v ON v.id = a.vacancy_id
             WHERE a.id = ?', [$id]
        );
        if (!$applicant) {
            redirect('applicants');
        }
        if ($applicant['employee_id']) {
            flash('error', 'This applicant has already been converted to an employee.');
            redirect('applicants');
        }
        if (!$applicant['gender']) {
            flash('error', 'Record the applicant\'s gender (edit the applicant) before converting.');
            redirect('applicants/' . $id . '/edit');
        }

        // Accepted offer (if any) supplies salary and start date.
        $offer = Database::fetch(
            "SELECT * FROM job_offers WHERE applicant_id = ? AND status = 'accepted'
             ORDER BY sent_at DESC LIMIT 1", [$id]
        );

        $parts = preg_split('/\s+/', trim($applicant['full_name']), 2);
        $max = (int) Database::scalar(
            "SELECT MAX(CAST(SUBSTRING(employee_no, 5) AS UNSIGNED)) FROM employees WHERE employee_no LIKE 'PTS-%'"
        );

        try {
            $employeeId = Database::insert('employees', [
                'employee_no'   => sprintf('PTS-%04d', $max + 1),
                'first_name'    => $parts[0],
                'last_name'     => $parts[1] ?? $parts[0],
                'gender'        => $applicant['gender'],
                'phone'         => $applicant['phone'],
                'email'         => $applicant['email'],
                'department_id' => $applicant['department_id'],
                'position_id'   => $applicant['position_id'],
                'employment_type' => $applicant['employment_type'],
                'date_hired'    => $offer['start_date'] ?? date('Y-m-d'),
                'basic_salary'  => $offer['salary'] ?? 0,
                'created_by'    => Auth::id(),
            ]);
        } catch (\PDOException $e) {
            flash('error', 'Could not create the employee (duplicate email?): ' . $e->getMessage());
            redirect('applicants');
        }

        Database::update('applicants', ['stage' => 'hired', 'employee_id' => $employeeId], $id);

        // Carry the CV into the document vault and open leave balances.
        if ($applicant['cv_path']) {
            Database::insert('employee_documents', [
                'employee_id' => $employeeId, 'doc_type' => 'cv',
                'title' => 'CV (from application)', 'file_path' => $applicant['cv_path'],
                'uploaded_by' => Auth::id(),
            ]);
        }
        Database::query(
            "INSERT INTO leave_balances (employee_id, leave_type_id, year, entitled)
             SELECT ?, id, YEAR(CURDATE()), days_per_year FROM leave_types
             WHERE is_active = 1 AND (gender = 'all' OR gender = ?)",
            [$employeeId, $applicant['gender']]
        );

        Audit::log('convert', 'applicants', (string) $id, null,
            ['employee_id' => $employeeId, 'stage' => 'hired']);
        flash('success', "Applicant hired — employee record created. Complete the remaining details.");
        redirect('employees/' . $employeeId . '/edit');
    }
}
