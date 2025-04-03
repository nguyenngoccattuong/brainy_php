<?php
namespace App\Models;

abstract class Model {
    protected $conn;
    protected $table;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Lấy kết nối database
     * 
     * @return \PDO
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Tạo UUID v4
     */
    protected function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Tạo bản ghi mới
     * 
     * @param array $data Dữ liệu cần tạo
     * @return string|bool UUID của bản ghi mới hoặc false nếu thất bại
     */
    public function create($data) {
        try {
            // Tạo UUID cho bản ghi mới
            $uuid = $this->generateUUID();
            $data['id'] = $uuid;
            
            // Thêm created_at và updated_at
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            
            $fields = array_keys($data);
            $placeholders = array_map(function($field) {
                return ":$field";
            }, $fields);
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
                    
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("SQL: " . $sql);
                error_log("Data: " . json_encode($data));
            }
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind các giá trị
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            if ($stmt->execute()) {
                return $uuid;
            }
            
            return false;
        } catch (\PDOException $e) {
            error_log("Create Error: " . $e->getMessage());
            throw new \Exception('Không thể tạo bản ghi: ' . $e->getMessage());
        }
    }
    
    /**
     * Lấy bản ghi theo ID
     * 
     * @param string $id UUID của bản ghi
     * @return array|bool Dữ liệu bản ghi hoặc false nếu không tìm thấy
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("GetById Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tìm một bản ghi theo điều kiện
     * 
     * @param array $conditions Mảng điều kiện [field => value]
     * @return array|bool Dữ liệu bản ghi hoặc false nếu không tìm thấy
     */
    public function findOneWhere($conditions) {
        try {
            $fields = array_keys($conditions);
            $where = implode(' = ? AND ', $fields) . ' = ?';
            
            $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array_values($conditions));
            
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("FindOneWhere Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cập nhật bản ghi
     * 
     * @param string $id UUID của bản ghi
     * @param array $data Dữ liệu cần cập nhật
     * @return bool
     */
    public function update($id, $data) {
        try {
            $fields = array_keys($data);
            $set = implode(' = ?, ', $fields) . ' = ?';
            
            $sql = "UPDATE {$this->table} SET {$set} WHERE id = ?";
            $values = array_values($data);
            $values[] = $id;
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($values);
        } catch (\PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Xóa bản ghi
     * 
     * @param string $id UUID của bản ghi
     * @return bool
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }
} 