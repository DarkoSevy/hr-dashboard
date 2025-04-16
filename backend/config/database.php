<?php
class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn;
    private $maxRetries = 3;
    private $retryDelay = 2;

    public function __construct() {
        // Get environment variables from Docker
        $this->host = getenv('DB_HOST') ?: 'mysql';  // Use the service name from docker-compose
        $this->dbname = getenv('DB_NAME') ?: 'hr_dashboard';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: 'root';
    }

    public function connect() {
        $retries = 0;
        $lastException = null;

        while ($retries < $this->maxRetries) {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname}",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
                return $this->conn;
            } catch(PDOException $e) {
                $lastException = $e;
                error_log("Connection attempt {$retries} failed: " . $e->getMessage());
                $retries++;
                if ($retries < $this->maxRetries) {
                    sleep($this->retryDelay);
                }
            }
        }

        error_log("Failed to connect to database after {$this->maxRetries} attempts");
        throw new Exception("Database connection failed: " . ($lastException ? $lastException->getMessage() : "Unknown error"));
    }
}
?> 