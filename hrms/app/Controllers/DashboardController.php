<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::require('dashboard.view');

        $cards = [
            'total_employees' => Database::scalar("SELECT COUNT(*) FROM employees WHERE status='active'"),
            'drivers'         => Database::scalar("SELECT COUNT(*) FROM employees WHERE status='active' AND is_driver=1"),
            'office_staff'    => Database::scalar("SELECT COUNT(*) FROM employees WHERE status='active' AND is_driver=0"),
            'on_leave_today'  => Database::scalar(
                "SELECT COUNT(*) FROM leave_requests WHERE status='approved' AND CURDATE() BETWEEN start_date AND end_date"),
            'pending_leave'   => Database::scalar(
                "SELECT COUNT(*) FROM leave_requests WHERE status IN ('pending_supervisor','pending_hr')"),
            'birthdays_month' => Database::scalar(
                "SELECT COUNT(*) FROM employees WHERE status='active' AND MONTH(date_of_birth)=MONTH(CURDATE())"),
            'new_this_month'  => Database::scalar(
                "SELECT COUNT(*) FROM employees WHERE date_hired >= DATE_FORMAT(CURDATE(),'%Y-%m-01')"),
            'open_vacancies'  => Database::scalar(
                "SELECT COUNT(*) FROM job_vacancies WHERE status='published'"),
            'attendance_today'=> Database::scalar(
                "SELECT COUNT(*) FROM attendance_records WHERE work_date=CURDATE() AND clock_in IS NOT NULL"),
            'late_today'      => Database::scalar(
                "SELECT COUNT(*) FROM attendance_records WHERE work_date=CURDATE() AND status='late'"),
            'contracts_expiring' => Database::scalar(
                "SELECT COUNT(*) FROM contracts WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)"),
            'licenses_expiring'  => Database::scalar(
                "SELECT COUNT(*) FROM drivers WHERE license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)"),
        ];

        $charts = [
            'departments' => Database::fetchAll(
                "SELECT d.name AS label, COUNT(e.id) AS value
                 FROM departments d LEFT JOIN employees e
                   ON e.department_id = d.id AND e.status='active'
                 GROUP BY d.id ORDER BY value DESC"),
            'gender' => Database::fetchAll(
                "SELECT gender AS label, COUNT(*) AS value FROM employees
                 WHERE status='active' GROUP BY gender"),
            'growth' => Database::fetchAll(
                "SELECT DATE_FORMAT(date_hired,'%Y-%m') AS label, COUNT(*) AS value
                 FROM employees WHERE date_hired >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY label ORDER BY label"),
            'age' => Database::fetchAll(
                "SELECT CASE
                    WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE()) < 25 THEN '18–24'
                    WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE()) < 35 THEN '25–34'
                    WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE()) < 45 THEN '35–44'
                    WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE()) < 55 THEN '45–54'
                    ELSE '55+' END AS label, COUNT(*) AS value
                 FROM employees WHERE status='active' AND date_of_birth IS NOT NULL
                 GROUP BY label ORDER BY label"),
            'leave_trend' => Database::fetchAll(
                "SELECT DATE_FORMAT(start_date,'%Y-%m') AS label, COUNT(*) AS value
                 FROM leave_requests WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY label ORDER BY label"),
            'attendance_trend' => Database::fetchAll(
                "SELECT work_date AS label,
                        SUM(status IN ('present','late')) AS value
                 FROM attendance_records
                 WHERE work_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                 GROUP BY work_date ORDER BY work_date"),
            'recruitment_funnel' => Database::fetchAll(
                "SELECT stage AS label, COUNT(*) AS value FROM applicants
                 GROUP BY stage
                 ORDER BY FIELD(stage,'applied','shortlisted','interview','offer','accepted','hired','rejected','talent_pool')"),
        ];

        $upcoming = [
            'birthdays' => Database::fetchAll(
                "SELECT CONCAT(first_name,' ',last_name) AS name,
                        DATE_FORMAT(date_of_birth,'%d %b') AS day
                 FROM employees WHERE status='active'
                   AND DATE_FORMAT(date_of_birth,'%m-%d')
                       BETWEEN DATE_FORMAT(CURDATE(),'%m-%d') AND DATE_FORMAT(DATE_ADD(CURDATE(),INTERVAL 30 DAY),'%m-%d')
                 ORDER BY DATE_FORMAT(date_of_birth,'%m-%d') LIMIT 6"),
            'expiring_contracts' => Database::fetchAll(
                "SELECT CONCAT(e.first_name,' ',e.last_name) AS name, c.end_date
                 FROM contracts c JOIN employees e ON e.id=c.employee_id
                 WHERE c.status='active' AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 60 DAY)
                 ORDER BY c.end_date LIMIT 6"),
            'expiring_licenses' => Database::fetchAll(
                "SELECT CONCAT(e.first_name,' ',e.last_name) AS name, d.license_expiry AS end_date
                 FROM drivers d JOIN employees e ON e.id=d.employee_id
                 WHERE d.license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 90 DAY)
                 ORDER BY d.license_expiry LIMIT 6"),
            'pending_leaves' => Database::fetchAll(
                "SELECT lr.id, CONCAT(e.first_name,' ',e.last_name) AS name,
                        lt.name AS leave_type, lr.start_date, lr.days, lr.status
                 FROM leave_requests lr
                 JOIN employees e ON e.id=lr.employee_id
                 JOIN leave_types lt ON lt.id=lr.leave_type_id
                 WHERE lr.status IN ('pending_supervisor','pending_hr')
                 ORDER BY lr.created_at DESC LIMIT 6"),
        ];

        $this->view('dashboard/index', compact('cards', 'charts', 'upcoming'));
    }
}
