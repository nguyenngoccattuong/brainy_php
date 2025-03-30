<?php
namespace App\Config;

/**
 * Class Database
 * Xử lý kết nối đến database MySQL/phpMyAdmin
 */
class Database {
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbName = $_ENV['DB_NAME'] ?? 'brainy';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? 'root';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
    }

    /**
     * Tạo kết nối đến database
     * @return \PDO
     */
    public function connect() {
        if ($this->conn) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset=utf8mb4";
            
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Connecting to database: {$this->host}:{$this->port}/{$this->dbName}");
            }
            
            $this->conn = new \PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Database connection successful");
            }
            
            return $this->conn;
        } catch (\PDOException $e) {
            $logFile = __DIR__ . '/../../logs/db_error.log';
            $message = date('Y-m-d H:i:s') . ' - Database Error: ' . $e->getMessage() . 
                "\nDSN: mysql:host={$this->host};port={$this->port};dbname={$this->dbName}\n" .
                "Trace: " . $e->getTraceAsString() . "\n\n";
            
            // Ensure log directory exists
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            // Log the error
            error_log($message);
            file_put_contents($logFile, $message, FILE_APPEND);
            
            // Return a JSON error response
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Kết nối database thất bại. Vui lòng kiểm tra lại cấu hình.'
            ]);
            exit;
        }
    }
} 