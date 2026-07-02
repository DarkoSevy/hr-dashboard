<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

/**
 * REST API (JSON): /api/v1/<resource>[/<id>]
 * Auth: "Authorization: Bearer <api_key>" — keys live on users.api_key.
 *
 *   GET    /api/v1/employees          list (filter with ?status=&department_id=)
 *   GET    /api/v1/employees/5        single record
 *   POST   /api/v1/employees          create (JSON body)
 *   PUT    /api/v1/employees/5        update (JSON body)
 *   DELETE /api/v1/employees/5        delete
 *   GET    /api/v1/stats              dashboard statistics
 *
 * Exposed resources are whitelisted below with their writable columns.
 */
class ApiController
{
    private const RESOURCES = [
        'employees' => ['table' => 'employees', 'writable' => [
            'first_name','last_name','national_id','gender','date_of_birth','marital_status',
            'nationality','phone','email','address','department_id','position_id','branch_id',
            'manager_id','employment_type','date_hired','status','basic_salary','rssb_number',
            'tin_number','is_driver','employee_no']],
        'departments' => ['table' => 'departments', 'writable' => ['name','code','branch_id','manager_id','description','is_active']],
        'positions' => ['table' => 'positions', 'writable' => ['title','department_id','reports_to','salary_min','salary_max','is_active']],
        'leaves' => ['table' => 'leave_requests', 'writable' => ['employee_id','leave_type_id','start_date','end_date','days','reason','status']],
        'attendance' => ['table' => 'attendance_records', 'writable' => ['employee_id','work_date','clock_in','clock_out','source','status']],
        'drivers' => ['table' => 'drivers', 'writable' => ['employee_id','license_number','license_categories','license_expiry','permit_expiry','rating','status']],
        'vehicles' => ['table' => 'vehicles', 'writable' => ['plate_number','make_model','vehicle_type','is_active']],
        'trainings' => ['table' => 'training_sessions', 'writable' => ['course_id','start_date','end_date','location','trainer','status']],
        'assets' => ['table' => 'assets', 'writable' => ['asset_tag','name','category','serial_no','status','value']],
        'vacancies' => ['table' => 'job_vacancies', 'writable' => ['title','department_id','employment_type','openings','deadline','status']],
        'notifications' => ['table' => 'notifications', 'writable' => ['user_id','type','title','body','link']],
    ];

    public function dispatch(array $segments): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            json_response(null, 204);
        }

        if (($segments[0] ?? '') !== 'v1') {
            json_response(['error' => 'Unknown API version. Use /api/v1/…'], 404);
        }

        $user = $this->authenticate();
        $resource = $segments[1] ?? '';
        $id = isset($segments[2]) && ctype_digit($segments[2]) ? (int) $segments[2] : null;
        $method = $_SERVER['REQUEST_METHOD'];

        if ($resource === 'stats' && $method === 'GET') {
            $this->stats();
        }
        if (!isset(self::RESOURCES[$resource])) {
            json_response(['error' => "Unknown resource '{$resource}'."], 404);
        }
        $cfg = self::RESOURCES[$resource];

        match (true) {
            $method === 'GET' && $id === null   => $this->list($cfg),
            $method === 'GET'                    => $this->get($cfg, $id),
            $method === 'POST'                   => $this->create($cfg, $user),
            $method === 'PUT' && $id !== null    => $this->update($cfg, $id, $user),
            $method === 'DELETE' && $id !== null => $this->delete($cfg, $id, $user),
            default => json_response(['error' => 'Method not allowed.'], 405),
        };
    }

    private function authenticate(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(\S+)$/', $header, $m)) {
            json_response(['error' => 'Missing Bearer token.'], 401);
        }
        $user = Database::fetch(
            'SELECT id, username, role_id FROM users WHERE api_key = ? AND is_active = 1',
            [hash('sha256', $m[1])]
        );
        if (!$user) {
            json_response(['error' => 'Invalid API key.'], 401);
        }
        return $user;
    }

    private function list(array $cfg): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 25)));
        $where = '1=1';
        $params = [];
        // Simple equality filters on whitelisted columns: ?status=active&department_id=2
        foreach ($_GET as $key => $value) {
            if (in_array($key, $cfg['writable'], true) && is_string($value)) {
                $where .= " AND `{$key}` = ?";
                $params[] = $value;
            }
        }
        $total = (int) Database::scalar("SELECT COUNT(*) FROM `{$cfg['table']}` WHERE {$where}", $params);
        $offset = ($page - 1) * $perPage;
        $rows = Database::fetchAll(
            "SELECT * FROM `{$cfg['table']}` WHERE {$where} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        json_response([
            'data' => $rows,
            'meta' => ['page' => $page, 'per_page' => $perPage, 'total' => $total],
        ]);
    }

    private function get(array $cfg, int $id): void
    {
        $row = Database::fetch("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id]);
        $row ? json_response(['data' => $row]) : json_response(['error' => 'Not found.'], 404);
    }

    private function create(array $cfg, array $user): void
    {
        $data = $this->body($cfg);
        if (!$data) {
            json_response(['error' => 'Empty or invalid JSON body.'], 422);
        }
        try {
            $id = Database::insert($cfg['table'], $data);
        } catch (\PDOException $e) {
            json_response(['error' => 'Database rejected the record.', 'detail' => $e->getMessage()], 422);
        }
        $this->audit($user, 'create', $cfg['table'], $id, $data);
        json_response(['data' => Database::fetch("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id])], 201);
    }

    private function update(array $cfg, int $id, array $user): void
    {
        $old = Database::fetch("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id]);
        if (!$old) {
            json_response(['error' => 'Not found.'], 404);
        }
        $data = $this->body($cfg);
        if (!$data) {
            json_response(['error' => 'Empty or invalid JSON body.'], 422);
        }
        try {
            Database::update($cfg['table'], $data, $id);
        } catch (\PDOException $e) {
            json_response(['error' => 'Database rejected the update.', 'detail' => $e->getMessage()], 422);
        }
        $this->audit($user, 'update', $cfg['table'], $id, $data);
        json_response(['data' => Database::fetch("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id])]);
    }

    private function delete(array $cfg, int $id, array $user): void
    {
        $old = Database::fetch("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [$id]);
        if (!$old) {
            json_response(['error' => 'Not found.'], 404);
        }
        Database::delete($cfg['table'], $id);
        $this->audit($user, 'delete', $cfg['table'], $id, null);
        json_response(['data' => null], 204);
    }

    private function stats(): void
    {
        json_response(['data' => [
            'total_employees' => (int) Database::scalar("SELECT COUNT(*) FROM employees WHERE status='active'"),
            'drivers' => (int) Database::scalar("SELECT COUNT(*) FROM employees WHERE status='active' AND is_driver=1"),
            'on_leave_today' => (int) Database::scalar(
                "SELECT COUNT(*) FROM leave_requests WHERE status='approved' AND CURDATE() BETWEEN start_date AND end_date"),
            'pending_leave_requests' => (int) Database::scalar(
                "SELECT COUNT(*) FROM leave_requests WHERE status IN ('pending_supervisor','pending_hr')"),
            'attendance_today' => (int) Database::scalar(
                'SELECT COUNT(*) FROM attendance_records WHERE work_date=CURDATE() AND clock_in IS NOT NULL'),
            'open_vacancies' => (int) Database::scalar("SELECT COUNT(*) FROM job_vacancies WHERE status='published'"),
            'by_department' => Database::fetchAll(
                "SELECT d.name, COUNT(e.id) AS employees FROM departments d
                 LEFT JOIN employees e ON e.department_id=d.id AND e.status='active'
                 GROUP BY d.id"),
        ]]);
    }

    private function body(array $cfg): array
    {
        $json = json_decode(file_get_contents('php://input') ?: '', true);
        if (!is_array($json)) {
            return [];
        }
        return array_intersect_key($json, array_flip($cfg['writable']));
    }

    private function audit(array $user, string $action, string $entity, int $id, ?array $new): void
    {
        Database::insert('audit_logs', [
            'user_id' => $user['id'],
            'action' => 'api_' . $action,
            'entity' => $entity,
            'entity_id' => (string) $id,
            'new_values' => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'api-client', 0, 255),
        ]);
    }
}
