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
        $this->host = $_ENV['DB_HOST'];
        $this->dbName = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
        $this->port = $_ENV['DB_PORT'];
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
            $this->conn = new \PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            
            return $this->conn;
        } catch (\PDOException $e) {
            $logFile = __DIR__ . '/../../logs/db_error.log';
            $message = date('Y-m-d H:i:s') . ' - Database Error: ' . $e->getMessage() . "\n";
            file_put_contents($logFile, $message, FILE_APPEND);
            
            die(json_encode([
                'status' => 'error',
                'message' => 'Kết nối database thất bại. Vui lòng kiểm tra lại cấu hình.'
            ]));
        }
    }
} 