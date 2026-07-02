<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/** Canned HR reports, viewable on screen and exportable as CSV. */
class ReportController extends Controller
{
    private const REPORTS = [
        'employees' => [
            'title' => 'Employee Register',
            'sql' => "SELECT e.employee_no AS 'Employee No', CONCAT(e.first_name,' ',e.last_name) AS Name,
                       e.gender AS Gender, d.name AS Department, p.title AS Position,
                       e.employment_type AS Type, e.date_hired AS 'Date Hired', e.status AS Status
                      FROM employees e
                      LEFT JOIN departments d ON d.id=e.department_id
                      LEFT JOIN positions p ON p.id=e.position_id
                      ORDER BY e.last_name",
        ],
        'gender' => [
            'title' => 'Gender Distribution',
            'sql' => "SELECT d.name AS Department,
                       SUM(e.gender='male') AS Male, SUM(e.gender='female') AS Female,
                       COUNT(e.id) AS Total
                      FROM departments d LEFT JOIN employees e
                        ON e.department_id=d.id AND e.status='active'
                      GROUP BY d.id ORDER BY Total DESC",
        ],
        'age' => [
            'title' => 'Age Analysis',
            'sql' => "SELECT CASE
                        WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE())<25 THEN '18-24'
                        WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE())<35 THEN '25-34'
                        WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE())<45 THEN '35-44'
                        WHEN TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE())<55 THEN '45-54'
                        ELSE '55+' END AS 'Age Band', COUNT(*) AS Employees
                      FROM employees WHERE status='active' AND date_of_birth IS NOT NULL
                      GROUP BY 1 ORDER BY 1",
        ],
        'leave' => [
            'title' => 'Leave Report (last 12 months)',
            'sql' => "SELECT CONCAT(e.first_name,' ',e.last_name) AS Employee, lt.name AS 'Leave Type',
                       lr.start_date AS 'Start', lr.end_date AS 'End', lr.days AS Days, lr.status AS Status
                      FROM leave_requests lr
                      JOIN employees e ON e.id=lr.employee_id
                      JOIN leave_types lt ON lt.id=lr.leave_type_id
                      WHERE lr.start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                      ORDER BY lr.start_date DESC",
        ],
        'attendance' => [
            'title' => 'Attendance Report (last 31 days)',
            'sql' => "SELECT ar.work_date AS Date, CONCAT(e.first_name,' ',e.last_name) AS Employee,
                       TIME(ar.clock_in) AS 'Clock In', TIME(ar.clock_out) AS 'Clock Out',
                       ar.status AS Status, ar.late_minutes AS 'Late (min)'
                      FROM attendance_records ar JOIN employees e ON e.id=ar.employee_id
                      WHERE ar.work_date >= DATE_SUB(CURDATE(), INTERVAL 31 DAY)
                      ORDER BY ar.work_date DESC, e.last_name",
        ],
        'late' => [
            'title' => 'Late Arrivals (last 31 days)',
            'sql' => "SELECT CONCAT(e.first_name,' ',e.last_name) AS Employee, d.name AS Department,
                       COUNT(*) AS 'Times Late', SUM(ar.late_minutes) AS 'Total Minutes'
                      FROM attendance_records ar
                      JOIN employees e ON e.id=ar.employee_id
                      LEFT JOIN departments d ON d.id=e.department_id
                      WHERE ar.status='late' AND ar.work_date >= DATE_SUB(CURDATE(), INTERVAL 31 DAY)
                      GROUP BY e.id ORDER BY 3 DESC",
        ],
        'turnover' => [
            'title' => 'Turnover (exits by month, last 12 months)',
            'sql' => "SELECT DATE_FORMAT(status_date,'%Y-%m') AS Month, status AS Reason, COUNT(*) AS Exits
                      FROM employees
                      WHERE status IN ('terminated','resigned','retired')
                        AND status_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                      GROUP BY 1,2 ORDER BY 1 DESC",
        ],
        'contracts' => [
            'title' => 'Contract Expiry (next 6 months)',
            'sql' => "SELECT CONCAT(e.first_name,' ',e.last_name) AS Employee, e.employee_no AS 'Employee No',
                       c.contract_type AS Type, c.end_date AS 'Expiry Date',
                       DATEDIFF(c.end_date, CURDATE()) AS 'Days Remaining'
                      FROM contracts c JOIN employees e ON e.id=c.employee_id
                      WHERE c.status='active' AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
                      ORDER BY c.end_date",
        ],
        'probation' => [
            'title' => 'Probation Ending (next 3 months)',
            'sql' => "SELECT CONCAT(first_name,' ',last_name) AS Employee, employee_no AS 'Employee No',
                       date_hired AS 'Date Hired', probation_end AS 'Probation Ends'
                      FROM employees
                      WHERE status='active' AND probation_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
                      ORDER BY probation_end",
        ],
        'birthdays' => [
            'title' => 'Birthday List (this month)',
            'sql' => "SELECT CONCAT(first_name,' ',last_name) AS Employee,
                       DATE_FORMAT(date_of_birth,'%d %M') AS Birthday,
                       TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE())+1 AS 'Turning'
                      FROM employees
                      WHERE status='active' AND MONTH(date_of_birth)=MONTH(CURDATE())
                      ORDER BY DAY(date_of_birth)",
        ],
        'recruitment' => [
            'title' => 'Recruitment Funnel',
            'sql' => "SELECT v.title AS Vacancy, a.stage AS Stage, COUNT(*) AS Applicants
                      FROM applicants a JOIN job_vacancies v ON v.id=a.vacancy_id
                      GROUP BY v.id, a.stage ORDER BY v.title,
                      FIELD(a.stage,'applied','shortlisted','interview','offer','accepted','hired','rejected','talent_pool')",
        ],
        'training' => [
            'title' => 'Training Report',
            'sql' => "SELECT tc.name AS Course, ts.start_date AS 'Start Date', ts.status AS Status,
                       COUNT(tp.id) AS Enrolled, SUM(tp.attended) AS Attended
                      FROM training_sessions ts
                      JOIN training_courses tc ON tc.id=ts.course_id
                      LEFT JOIN training_participants tp ON tp.session_id=ts.id
                      GROUP BY ts.id ORDER BY ts.start_date DESC",
        ],
        'drivers' => [
            'title' => 'Driver Performance Ranking',
            'sql' => "SELECT CONCAT(e.first_name,' ',e.last_name) AS Driver, d.license_number AS License,
                       d.license_expiry AS 'License Expiry', d.rating AS Rating, d.trips_completed AS Trips,
                       (SELECT COUNT(*) FROM driver_incidents di WHERE di.driver_id=d.id AND di.incident_type='accident') AS Accidents,
                       (SELECT COUNT(*) FROM driver_incidents di WHERE di.driver_id=d.id AND di.incident_type='traffic_fine') AS Fines
                      FROM drivers d JOIN employees e ON e.id=d.employee_id
                      ORDER BY d.rating DESC",
        ],
        'performance' => [
            'title' => 'Performance Summary',
            'sql' => "SELECT CONCAT(e.first_name,' ',e.last_name) AS Employee, pr.period AS Period,
                       pr.review_type AS Type, pr.kpi_score AS 'KPI Score',
                       pr.overall_rating AS Rating, pr.recommendation AS Recommendation
                      FROM performance_reviews pr JOIN employees e ON e.id=pr.employee_id
                      ORDER BY pr.created_at DESC",
        ],
    ];

    public function index(): void
    {
        Auth::require('reports.view');
        $key = $_GET['report'] ?? null;
        $report = null;
        $rows = [];
        if ($key !== null && isset(self::REPORTS[$key])) {
            $report = self::REPORTS[$key];
            $rows = Database::fetchAll($report['sql']);
        }
        $this->view('reports/index', [
            'reports' => array_map(fn($r) => $r['title'], self::REPORTS),
            'active' => $key,
            'report' => $report,
            'rows' => $rows,
        ]);
    }

    public function export(): void
    {
        Auth::require('reports.view');
        $key = $_GET['report'] ?? '';
        if (!isset(self::REPORTS[$key])) {
            redirect('reports');
        }
        $rows = Database::fetchAll(self::REPORTS[$key]['sql']);
        Audit::log('export', 'reports', $key);
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=report_{$key}_" . date('Ymd') . '.csv');
        $out = fopen('php://output', 'w');
        if ($rows) {
            fputcsv($out, array_keys($rows[0]), ",", '"', "\\");
            foreach ($rows as $row) {
                fputcsv($out, $row, ",", '"', "\\");
            }
        }
        fclose($out);
        exit;
    }
}
