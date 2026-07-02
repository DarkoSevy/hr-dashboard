<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/** Organization chart: department structure + employee reporting tree. */
class OrgChartController extends Controller
{
    public function index(): void
    {
        Auth::require('employees.view');

        $departments = Database::fetchAll(
            "SELECT d.*, b.name AS branch,
                    CONCAT(m.first_name,' ',m.last_name) AS manager_name,
                    (SELECT COUNT(*) FROM employees e
                      WHERE e.department_id = d.id AND e.status = 'active') AS headcount
             FROM departments d
             LEFT JOIN branches b ON b.id = d.branch_id
             LEFT JOIN employees m ON m.id = d.manager_id
             WHERE d.is_active = 1
             ORDER BY d.name"
        );

        $employees = Database::fetchAll(
            "SELECT e.id, e.manager_id, CONCAT(e.first_name,' ',e.last_name) AS name,
                    e.employee_no, e.photo_path, p.title AS position, d.name AS department
             FROM employees e
             LEFT JOIN positions p ON p.id = e.position_id
             LEFT JOIN departments d ON d.id = e.department_id
             WHERE e.status = 'active'
             ORDER BY e.first_name"
        );

        // Build the reporting tree in memory (manager_id → children).
        $children = [];
        foreach ($employees as $emp) {
            $children[(int) ($emp['manager_id'] ?? 0)][] = $emp;
        }

        $this->view('orgchart/index', [
            'departments' => $departments,
            'children' => $children,
        ]);
    }
}
