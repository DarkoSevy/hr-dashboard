<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

class JwtMiddleware {
    private static $secretKey;

    public static function init() {
        self::$secretKey = getenv('JWT_SECRET') ?: 'your-secret-key';
    }

    public static function authenticate() {
        self::init();
        
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }
        
        try {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            $decoded = JWT::decode($token, self::$secretKey, ['HS256']);
            return $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
    }

    public static function generateToken($userId, $userRole) {
        self::init();
        
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId,
            'role' => $userRole
        ];
        
        return JWT::encode($payload, self::$secretKey, 'HS256');
    }
} 