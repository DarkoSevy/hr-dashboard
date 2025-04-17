<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware {
    private static $secretKey;
    private static $algorithm = 'HS256';

    public static function init() {
        try {
            $secretKey = getenv('JWT_SECRET');
            
            if (!$secretKey) {
                error_log('JWT_SECRET environment variable not set');
                throw new Exception('JWT configuration error');
            }
            
            self::$secretKey = $secretKey;
        } catch (Exception $e) {
            error_log("JWT init error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function authenticate() {
        try {
            self::init();
            
            $headers = getallheaders();
            if (!isset($headers['Authorization'])) {
                error_log('No authorization token provided');
                http_response_code(401);
                echo json_encode(['error' => 'No token provided']);
                exit;
            }
            
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            
            // Verify token hasn't expired
            if (isset($decoded->exp) && $decoded->exp < time()) {
                error_log('Token has expired');
                http_response_code(401);
                echo json_encode(['error' => 'Token has expired']);
                exit;
            }
            
            return $decoded;
        } catch (Exception $e) {
            error_log("Token validation failed: " . $e->getMessage());
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }
    }

    public static function generateToken($userId, $userRole) {
        try {
            self::init();
            
            $issuedAt = time();
            $expiration = getenv('JWT_EXPIRATION');
            if (!$expiration) {
                error_log('JWT_EXPIRATION not set, using default 24 hours');
                $expiration = 86400; // 24 hours default
            }
            
            $expirationTime = $issuedAt + (int)$expiration;
            
            $payload = [
                'iat' => $issuedAt,
                'exp' => $expirationTime,
                'user_id' => $userId,
                'role' => $userRole
            ];
            
            return JWT::encode($payload, self::$secretKey, self::$algorithm);
        } catch (Exception $e) {
            error_log("Failed to generate token: " . $e->getMessage());
            throw $e;
        }
    }
}