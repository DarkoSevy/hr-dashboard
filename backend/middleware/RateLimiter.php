<?php
class RateLimiter {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
    }
    
    public function checkLimit($ip, $limit = 100, $window = 3600) {
        $key = "rate_limit:$ip";
        $current = $this->redis->incr($key);
        if ($current === 1) {
            $this->redis->expire($key, $window);
        }
        return $current <= $limit;
    }
} 