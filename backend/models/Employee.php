<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/RedisClient.php';

class Employee {
    private $conn;
    private $table = 'employees';
    private $redis;
    private $cacheExpiry = 3600; // 1 hour cache expiry
    private $useCache = true;
    
    // Cache key prefixes
    private const CACHE_KEY_ALL = 'employees:all';
    private const CACHE_KEY_EMPLOYEE = 'employees:id:';
    private const CACHE_KEY_STATS = 'employees:stats:';
    private const CACHE_KEY_METRICS = 'employees:metrics:';

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->connect();
            
            try {
                $this->redis = RedisClient::getInstance();
            } catch (Exception $e) {
                error_log("Redis initialization failed: " . $e->getMessage());
                $this->useCache = false;
            }
        } catch (Exception $e) {
            error_log("Employee Model Error: " . $e->getMessage());
            throw new Exception("Failed to initialize Employee model");
        }
    }

    private function getFromCache($key) {
        if (!$this->useCache) return null;
        try {
            return $this->redis->get($key);
        } catch (Exception $e) {
            error_log("Redis get failed for key {$key}: " . $e->getMessage());
            return null;
        }
    }

    private function setInCache($key, $value, $expiry = null) {
        if (!$this->useCache) return false;
        try {
            return $this->redis->set($key, $value, $expiry ?: $this->cacheExpiry);
        } catch (Exception $e) {
            error_log("Redis set failed for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    private function deleteFromCache($key) {
        if (!$this->useCache) return false;
        try {
            return $this->redis->delete($key);
        } catch (Exception $e) {
            error_log("Redis delete failed for key {$key}: " . $e->getMessage());
            return false;
        }
    }

    private function clearListCache() {
        $this->deleteFromCache(self::CACHE_KEY_ALL);
    }

    private function clearStatsCache() {
        $this->deleteFromCache(self::CACHE_KEY_STATS . 'total');
        $this->deleteFromCache(self::CACHE_KEY_STATS . 'active');
        $this->deleteFromCache(self::CACHE_KEY_STATS . 'all');
        $this->deleteFromCache(self::CACHE_KEY_STATS . 'new_hires');
        $this->deleteFromCache(self::CACHE_KEY_STATS . 'resigned');
    }

    private function clearMetricsCache() {
        $this->deleteFromCache(self::CACHE_KEY_METRICS . 'growth');
        $this->deleteFromCache(self::CACHE_KEY_METRICS . 'monthly_attendance');
        $this->deleteFromCache(self::CACHE_KEY_METRICS . 'work_format');
    }

    public function getAll() {
        try {
            // Try to get from cache first
            $cached = $this->getFromCache(self::CACHE_KEY_ALL);
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT * FROM " . $this->table . " WHERE deleted_at IS NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Cache the result
            $this->setInCache(self::CACHE_KEY_ALL, $employees);
            return $employees;
        } catch (PDOException $e) {
            error_log("Get All Employees Error: " . $e->getMessage());
            return []; // Return empty array instead of throwing to handle gracefully
        }
    }

    public function getById($id) {
        try {
            // $cacheKey = self::CACHE_KEY_EMPLOYEE . $id;
            
            // Try to get from cache first
            // $cached = $this->redis->get($cacheKey);
            // if ($cached !== null) {
            //     return $cached;
            // }

            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            // if ($employee) {
            //     // Cache the result
            //     $this->redis->set($cacheKey, $employee, $this->cacheExpiry);
            // }
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
            // if ($result) {
            //     // Only clear relevant caches
            //     $this->clearListCache();
            //     $this->clearStatsCache();
            // }
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
            // if ($result) {
            //     // Clear individual employee cache
            //     $this->redis->delete(self::CACHE_KEY_EMPLOYEE . $id);
                
            //     // Clear list cache only if relevant fields changed
            //     if ($this->hasRelevantChanges($currentEmployee, $data)) {
            //         $this->clearListCache();
            //     }
                
            //     // Clear stats cache only if status changed
            //     if ($currentEmployee['status'] !== $data['status']) {
            //         $this->clearStatsCache();
            //         $this->clearMetricsCache();
            //     }
            // }
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
            // Try to get from cache first
            $cached = $this->getFromCache(self::CACHE_KEY_STATS . 'total');
            if ($cached !== null) {
                return (int)$cached;
            }

            // If not in cache, calculate from database
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE deleted_at IS NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)$result['total'];

            // Cache the result
            $this->setInCache(self::CACHE_KEY_STATS . 'total', $total);

            return $total;
        } catch (Exception $e) {
            error_log("Error getting total employee count: " . $e->getMessage());
            throw new Exception("Failed to get total employee count");
        }
    }

    public function getActiveCount() {
        try {
            // Try to get from cache first
            $cached = $this->getFromCache(self::CACHE_KEY_STATS . 'active');
            if ($cached !== null) {
                return (int)$cached;
            }

            // If not in cache, calculate from database
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                     WHERE status = 'active' AND deleted_at IS NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)$result['total'];

            // Cache the result
            $this->setInCache(self::CACHE_KEY_STATS . 'active', $total);

            return $total;
        } catch (Exception $e) {
            error_log("Error getting active employee count: " . $e->getMessage());
            throw new Exception("Failed to get active employee count");
        }
    }

    public function getStats() {
        try {
            // Try to get from cache first
            $cached = $this->getFromCache(self::CACHE_KEY_STATS . 'all');
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' AND deleted_at IS NULL THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status != 'active' OR deleted_at IS NOT NULL THEN 1 ELSE 0 END) as inactive
            FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats = [
                'total' => (int)$result['total'],
                'active' => (int)$result['active'],
                'inactive' => (int)$result['inactive']
            ];

            // Cache the result
            $this->setInCache(self::CACHE_KEY_STATS . 'all', $stats);

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting employee stats: " . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0
            ];
        }
    }

    public function getMonthlyAttendance() {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
                FROM attendance
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 6";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWorkFormatDistribution() {
        $sql = "SELECT 
                    work_format,
                    COUNT(*) as count
                FROM employees
                GROUP BY work_format";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewHiresCount() {
        try {
            // Try to get from cache first
            $cached = $this->getFromCache('employees:new_hires');
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
            $this->setInCache('employees:new_hires', $total);
            return $total;
        } catch (PDOException $e) {
            error_log("Get New Hires Count Error: " . $e->getMessage());
            throw new Exception("Failed to get new hires count");
        }
    }

    public function getResignedCount() {
        try {
            // Try to get from cache first
            $cached = $this->getFromCache('employees:resigned');
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
            $this->setInCache('employees:resigned', $total);
            return $total;
        } catch (PDOException $e) {
            error_log("Get Resigned Count Error: " . $e->getMessage());
            throw new Exception("Failed to get resigned count");
        }
    }

    public function calculateGrowth() {
        try {
            // Try to get from cache first
            $cached = $this->getFromCache('employees:growth');
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
            $this->setInCache('employees:growth', $growth);
            return $growth;
        } catch (PDOException $e) {
            error_log("Calculate Growth Error: " . $e->getMessage());
            throw new Exception("Failed to calculate growth");
        }
    }
}
?> 