<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/RedisClient.php';
require_once __DIR__ . '/../middleware/JwtMiddleware.php';

class User {
    private $conn;
    private $table = 'users';
    private $redis;
    private $cacheExpiry = 3600; // 1 hour cache expiry
    private $useCache = true;
    
    // Cache key prefixes
    private const CACHE_KEY_USER = 'users:id:';
    private const CACHE_KEY_USERNAME = 'users:username:';
    private const CACHE_KEY_EMAIL = 'users:email:';

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
            error_log("User Model Error: " . $e->getMessage());
            throw new Exception("Failed to initialize User model");
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

    public function authenticate($username, $password) {
        try {
            error_log("Authenticating user: " . $username);
            
            $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("User data found: " . json_encode($user));
            
            if ($user) {
                error_log("Verifying password for user: " . $username);
                if (password_verify($password, $user['password'])) {
                    // Generate JWT token
                    $token = JwtMiddleware::generateToken($user['id'], $user['role']);
                    error_log("Generated token: " . $token);
                    
                    // Remove sensitive data
                    unset($user['password']);
                    
                    $response = [
                        'user' => $user,
                        'token' => $token
                    ];
                    error_log("Authentication successful. Response: " . json_encode($response));
                    return $response;
                } else {
                    error_log("Password verification failed for user: " . $username);
                    return false;
                }
            } else {
                error_log("User not found: " . $username);
                return false;
            }
        } catch (Exception $e) {
            error_log("Authentication Error: " . $e->getMessage());
            throw new Exception("Authentication failed: " . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $cacheKey = self::CACHE_KEY_USER . $id;
            $cached = $this->getFromCache($cacheKey);
            if ($cached !== null) {
                return $cached;
            }

            $query = "SELECT id, username, email, role, employee_id, created_at, updated_at 
                     FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $this->setInCache($cacheKey, $user);
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Get User By ID Error: " . $e->getMessage());
            throw new Exception("Failed to get user");
        }
    }

    public function create($data) {
        try {
            // Check if username or email already exists
            $query = "SELECT COUNT(*) FROM " . $this->table . " 
                     WHERE username = :username OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO " . $this->table . " 
                    (username, email, password, role, employee_id) 
                    VALUES (:username, :email, :password, :role, :employee_id)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':employee_id', $data['employee_id'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $userId = $this->conn->lastInsertId();
                return $this->getById($userId);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Create User Error: " . $e->getMessage());
            throw new Exception("Failed to create user");
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET ";
            $params = [];
            
            // Build dynamic update query
            foreach ($data as $key => $value) {
                if ($key === 'password') {
                    $value = password_hash($value, PASSWORD_DEFAULT);
                }
                $params[$key] = $value;
                $query .= "$key = :$key, ";
            }
            
            $query = rtrim($query, ', ');
            $query .= " WHERE id = :id";
            $params['id'] = $id;
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => &$value) {
                $stmt->bindParam(":$key", $value);
            }
            
            if ($stmt->execute()) {
                // Clear cache
                $this->deleteFromCache(self::CACHE_KEY_USER . $id);
                return $this->getById($id);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
            throw new Exception("Failed to update user");
        }
    }
}
?> 