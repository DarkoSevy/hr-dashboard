<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Redis.php';

class Employee {
    private $conn;
    private $table = 'employees';
    private $redis;
    private $cacheExpiry = 3600; // 1 hour cache expiry
    
    // Cache key prefixes
    private const CACHE_KEY_ALL = 'employees:all';
    private const CACHE_KEY_EMPLOYEE = 'employees:id:';
    private const CACHE_KEY_STATS = 'employees:stats:';
    private const CACHE_KEY_METRICS = 'employees:metrics:';

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->connect();
            $this->redis = Redis::getInstance();
        } catch (Exception $e) {
            error_log("Employee Model Error: " . $e->getMessage());
            throw new Exception("Failed to initialize Employee model");
        }
    }

    private function clearListCache() {
        // Clear only list-related caches
        $this->redis->delete(self::CACHE_KEY_ALL);
    }

    private function clearStatsCache() {
        // Clear statistical caches
        $this->redis->delete(self::CACHE_KEY_STATS . 'total');
        $this->redis->delete(self::CACHE_KEY_STATS . 'new_hires');
        $this->redis->delete(self::CACHE_KEY_STATS . 'resigned');
    }

    private function clearMetricsCache() {
        // Clear metrics caches
        $this->redis->delete(self::CACHE_KEY_METRICS . 'growth');
        $this->redis->delete(self::CACHE_KEY_METRICS . 'monthly_attendance');
        $this->redis->delete(self::CACHE_KEY_METRICS . 'work_format');
    }

    public function getAll() {
        try {
            // Try to get from cache first
            $cached = $this->redis->get(self::CACHE_KEY_ALL);
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT * FROM " . $this->table;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cache the result with pipeline for better performance
            $this->redis->set(self::CACHE_KEY_ALL, $employees, $this->cacheExpiry);
            return $employees;
        } catch (PDOException $e) {
            error_log("Get All Employees Error: " . $e->getMessage());
            return []; // Return empty array instead of throwing to handle gracefully
        }
    }

    public function getById($id) {
        try {
            $cacheKey = self::CACHE_KEY_EMPLOYEE . $id;
            
            // Try to get from cache first
            $cached = $this->redis->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                // Cache the result
                $this->redis->set($cacheKey, $employee, $this->cacheExpiry);
            }
            return $employee;
        } catch (PDOException $e) {
            error_log("Get Employee By ID Error: " . $e->getMessage());
            return null; // Return null instead of throwing to handle gracefully
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                    (name, email, position, department, start_date, salary, status, third_party_info) 
                    VALUES (:name, :email, :position, :department, :start_date, :salary, :status, :third_party_info)";
            
            $stmt = $this->conn->prepare($query);
            
            $third_party_info = isset($data['third_party_info']) ? json_encode($data['third_party_info']) : null;
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':salary', $data['salary']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':third_party_info', $third_party_info);
            
            $result = $stmt->execute();
            if ($result) {
                // Only clear relevant caches
                $this->clearListCache();
                $this->clearStatsCache();
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Create Employee Error: " . $e->getMessage());
            throw new Exception("Failed to create employee");
        }
    }

    public function update($id, $data) {
        try {
            // Get current employee data for comparison
            $currentEmployee = $this->getById($id);
            
            $query = "UPDATE " . $this->table . " 
                    SET name = :name, 
                        email = :email, 
                        position = :position, 
                        department = :department, 
                        start_date = :start_date, 
                        salary = :salary, 
                        status = :status,
                        third_party_info = :third_party_info
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $third_party_info = isset($data['third_party_info']) ? json_encode($data['third_party_info']) : null;
            
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':position', $data['position']);
            $stmt->bindParam(':department', $data['department']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':salary', $data['salary']);
            $stmt->bindParam(':status', $data['status']);
            $stmt->bindParam(':third_party_info', $third_party_info);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            if ($result) {
                // Clear individual employee cache
                $this->redis->delete(self::CACHE_KEY_EMPLOYEE . $id);
                
                // Clear list cache only if relevant fields changed
                if ($this->hasRelevantChanges($currentEmployee, $data)) {
                    $this->clearListCache();
                }
                
                // Clear stats cache only if status changed
                if ($currentEmployee['status'] !== $data['status']) {
                    $this->clearStatsCache();
                    $this->clearMetricsCache();
                }
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Update Employee Error: " . $e->getMessage());
            throw new Exception("Failed to update employee");
        }
    }

    private function hasRelevantChanges($old, $new) {
        $relevantFields = ['name', 'position', 'department', 'status'];
        foreach ($relevantFields as $field) {
            if ($old[$field] !== $new[$field]) {
                return true;
            }
        }
        return false;
    }

    public function getTotalCount() {
        try {
            $cacheKey = self::CACHE_KEY_STATS . 'total';
            
            // Try to get from cache first
            $cached = $this->redis->get($cacheKey);
            if ($cached !== null) {
                return (int)$cached;
            }

            $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE status = 'active'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)$result['total'];

            // Cache the result with a shorter expiry for frequently changing data
            $this->redis->set($cacheKey, $total, 1800); // 30 minutes
            return $total;
        } catch (PDOException $e) {
            error_log("Get Total Count Error: " . $e->getMessage());
            return 0; // Return 0 instead of throwing to handle gracefully
        }
    }

    public function getNewHiresCount() {
        try {
            // Try to get from cache first
            $cached = $this->redis->get('employees:new_hires');
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT COUNT(*) as total FROM " . $this->table . "
                     WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     AND status = 'active'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'];

            // Cache the result
            $this->redis->set('employees:new_hires', $total, $this->cacheExpiry);
            return $total;
        } catch (PDOException $e) {
            error_log("Get New Hires Count Error: " . $e->getMessage());
            throw new Exception("Failed to get new hires count");
        }
    }

    public function getResignedCount() {
        try {
            // Try to get from cache first
            $cached = $this->redis->get('employees:resigned');
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT COUNT(*) as total FROM " . $this->table . "
                     WHERE status = 'resigned' 
                     AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'];

            // Cache the result
            $this->redis->set('employees:resigned', $total, $this->cacheExpiry);
            return $total;
        } catch (PDOException $e) {
            error_log("Get Resigned Count Error: " . $e->getMessage());
            throw new Exception("Failed to get resigned count");
        }
    }

    public function calculateGrowth() {
        try {
            // Try to get from cache first
            $cached = $this->redis->get('employees:growth');
            if ($cached !== null) {
                return $cached;
            }

            $currentTotal = $this->getTotalCount();
            $query = "SELECT COUNT(*) as total FROM " . $this->table . "
                     WHERE start_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     AND status = 'active'";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $prevTotal = $result['total'];

            $growth = $prevTotal > 0 ? 
                round((($currentTotal - $prevTotal) / $prevTotal) * 100, 1) : 0;

            // Cache the result
            $this->redis->set('employees:growth', $growth, $this->cacheExpiry);
            return $growth;
        } catch (PDOException $e) {
            error_log("Calculate Growth Error: " . $e->getMessage());
            throw new Exception("Failed to calculate growth");
        }
    }

    public function getMonthlyAttendance() {
        try {
            // Try to get from cache first
            $cached = $this->redis->get('employees:monthly_attendance');
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT 
                        DATE_FORMAT(date, '%Y-%m') as month,
                        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
                    FROM attendance
                    WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(date, '%Y-%m')
                    ORDER BY month";

            $stmt = $this->conn->query($query);
            $data = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = [
                    'month' => date('M Y', strtotime($row['month'] . '-01')),
                    'present' => (int)$row['present'],
                    'absent' => (int)$row['absent']
                ];
            }

            // Cache the result
            $this->redis->set('employees:monthly_attendance', $data, $this->cacheExpiry);
            return $data;
        } catch (PDOException $e) {
            error_log("Get Monthly Attendance Error: " . $e->getMessage());
            throw new Exception("Failed to get monthly attendance");
        }
    }

    public function getWorkFormatDistribution() {
        try {
            $cacheKey = self::CACHE_KEY_METRICS . 'work_format';
            
            // Try to get from cache first
            $cached = $this->redis->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT 
                        work_format as format,
                        COUNT(*) as count
                    FROM " . $this->table . "
                    WHERE status = 'active'
                    GROUP BY work_format";

            $stmt = $this->conn->query($query);
            $data = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = [
                    'name' => ucfirst($row['format']),
                    'value' => (int)$row['count']
                ];
            }

            // Cache the result
            $this->redis->set($cacheKey, $data, $this->cacheExpiry);
            return $data;
        } catch (PDOException $e) {
            error_log("Get Work Format Distribution Error: " . $e->getMessage());
            return []; // Return empty array instead of throwing to handle gracefully
        }
    }
}
?> 