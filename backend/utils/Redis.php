<?php

class Redis {
    private static $instance = null;
    private $redis;
    private $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $this->redis = new \Redis();
            $this->redis->connect(
                $this->config['redis']['host'],
                $this->config['redis']['port']
            );
            
            // Set prefix for all keys
            if (isset($this->config['redis']['options']['prefix'])) {
                $this->redis->setOption(\Redis::OPT_PREFIX, $this->config['redis']['options']['prefix']);
            }
        } catch (\Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            throw new \Exception("Failed to connect to Redis server");
        }
    }

    public function set($key, $value, $expireTime = null) {
        try {
            if ($expireTime !== null) {
                return $this->redis->setex($key, $expireTime, serialize($value));
            }
            return $this->redis->set($key, serialize($value));
        } catch (\Exception $e) {
            error_log("Redis set operation failed: " . $e->getMessage());
            return false;
        }
    }

    public function get($key) {
        try {
            $value = $this->redis->get($key);
            return $value ? unserialize($value) : null;
        } catch (\Exception $e) {
            error_log("Redis get operation failed: " . $e->getMessage());
            return null;
        }
    }

    public function delete($key) {
        try {
            return $this->redis->del($key);
        } catch (\Exception $e) {
            error_log("Redis delete operation failed: " . $e->getMessage());
            return false;
        }
    }

    public function exists($key) {
        try {
            return $this->redis->exists($key);
        } catch (\Exception $e) {
            error_log("Redis exists operation failed: " . $e->getMessage());
            return false;
        }
    }

    public function flush() {
        try {
            return $this->redis->flushDB();
        } catch (\Exception $e) {
            error_log("Redis flush operation failed: " . $e->getMessage());
            return false;
        }
    }

    public function setHash($key, $field, $value) {
        try {
            return $this->redis->hSet($key, $field, serialize($value));
        } catch (\Exception $e) {
            error_log("Redis hash set operation failed: " . $e->getMessage());
            return false;
        }
    }

    public function getHash($key, $field) {
        try {
            $value = $this->redis->hGet($key, $field);
            return $value ? unserialize($value) : null;
        } catch (\Exception $e) {
            error_log("Redis hash get operation failed: " . $e->getMessage());
            return null;
        }
    }

    public function getAllHash($key) {
        try {
            $hash = $this->redis->hGetAll($key);
            return array_map('unserialize', $hash);
        } catch (\Exception $e) {
            error_log("Redis get all hash operation failed: " . $e->getMessage());
            return [];
        }
    }
} 