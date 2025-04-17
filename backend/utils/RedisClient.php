<?php

class RedisClient {
    private static $instance = null;
    private $connection = null;
    private $isConnected = false;
    private $maxRetries = 3;
    private $retryDelay = 1; // seconds

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        $retries = 0;
        while ($retries < $this->maxRetries) {
            try {
                $this->connection = new \Redis();
                $this->connection->connect(
                    getenv('REDIS_HOST') ?: 'redis',
                    getenv('REDIS_PORT') ?: 6379,
                    2.0, // timeout in seconds
                    null, // persistent_id
                    100, // retry_interval in milliseconds
                    0.0 // read_timeout in seconds
                );
                
                // Set memory limits and policies
                $this->connection->config('SET', 'maxmemory', '256mb');
                $this->connection->config('SET', 'maxmemory-policy', 'allkeys-lru');
                
                $this->isConnected = true;
                return;
            } catch (Exception $e) {
                $retries++;
                if ($retries === $this->maxRetries) {
                    error_log("Failed to connect to Redis after {$this->maxRetries} attempts: " . $e->getMessage());
                    $this->isConnected = false;
                    return;
                }
                sleep($this->retryDelay);
            }
        }
    }

    public function get($key) {
        if (!$this->isConnected) {
            return null;
        }

        try {
            return $this->connection->get($key);
        } catch (Exception $e) {
            error_log("Redis get error: " . $e->getMessage());
            return null;
        }
    }

    public function set($key, $value, $ttl = 3600) {
        if (!$this->isConnected) {
            return false;
        }

        try {
            return $this->connection->set($key, $value, $ttl);
        } catch (Exception $e) {
            error_log("Redis set error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($key) {
        if (!$this->isConnected) {
            return false;
        }

        try {
            return $this->connection->del($key);
        } catch (Exception $e) {
            error_log("Redis delete error: " . $e->getMessage());
            return false;
        }
    }

    public function isConnected() {
        return $this->isConnected;
    }

    public function __destruct() {
        if ($this->connection !== null) {
            try {
                $this->connection->close();
            } catch (Exception $e) {
                error_log("Redis connection close error: " . $e->getMessage());
            }
        }
    }

    private function safeSerialize($value) {
        try {
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return (string)$value;
        } catch (\Exception $e) {
            error_log("Redis serialization failed: " . $e->getMessage());
            return null;
        }
    }

    private function safeUnserialize($value) {
        try {
            if ($value === null || $value === false) {
                return null;
            }
            $decoded = json_decode($value, true);
            return ($decoded !== null && json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
        } catch (\Exception $e) {
            error_log("Redis unserialization failed: " . $e->getMessage());
            return null;
        }
    }

    public function setHash($key, $field, $value) {
        try {
            $serialized = $this->safeSerialize($value);
            if ($serialized === null) {
                return false;
            }
            return $this->connection->hSet($key, $field, $serialized);
        } catch (\Exception $e) {
            error_log("Redis setHash operation failed: " . $e->getMessage());
            return false;
        }
    }

    public function getHash($key, $field) {
        try {
            $value = $this->connection->hGet($key, $field);
            return $this->safeUnserialize($value);
        } catch (\Exception $e) {
            error_log("Redis getHash operation failed: " . $e->getMessage());
            return null;
        }
    }

    public function getAllHash($key) {
        try {
            $values = $this->connection->hGetAll($key);
            if (!is_array($values)) {
                return [];
            }
            
            $result = [];
            foreach ($values as $field => $value) {
                $result[$field] = $this->safeUnserialize($value);
            }
            return $result;
        } catch (\Exception $e) {
            error_log("Redis getAllHash operation failed: " . $e->getMessage());
            return [];
        }
    }
} 