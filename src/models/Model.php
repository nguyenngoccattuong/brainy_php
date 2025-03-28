<?php
namespace App\Models;

/**
 * Class Model
 * Base model class với các phương thức cơ bản cho các model khác kế thừa
 */
abstract class Model {
    protected $conn;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Lấy tất cả bản ghi
     * 
     * @param array $columns Các cột cần lấy
     * @param string $orderBy Sắp xếp theo cột
     * @param string $direction Hướng sắp xếp (ASC/DESC)
     * @return array
     */
    public function getAll($columns = ['*'], $orderBy = 'created_at', $direction = 'DESC') {
        $columnsStr = $columns[0] === '*' ? '*' : implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy bản ghi theo ID
     * 
     * @param string $id UUID của bản ghi
     * @param array $columns Các cột cần lấy
     * @return array|bool
     */
    public function getById($id, $columns = ['*']) {
        $columnsStr = $columns[0] === '*' ? '*' : implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Tìm bản ghi theo điều kiện
     * 
     * @param array $conditions Điều kiện tìm kiếm (column => value)
     * @param array $columns Các cột cần lấy
     * @return array
     */
    public function findWhere($conditions, $columns = ['*']) {
        $columnsStr = $columns[0] === '*' ? '*' : implode(', ', $columns);
        
        $whereStr = [];
        foreach (array_keys($conditions) as $column) {
            $whereStr[] = "{$column} = :{$column}";
        }
        
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE " . implode(' AND ', $whereStr);
        $stmt = $this->conn->prepare($sql);
        
        foreach ($conditions as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tìm một bản ghi theo điều kiện
     * 
     * @param array $conditions Điều kiện tìm kiếm (column => value)
     * @param array $columns Các cột cần lấy
     * @return array|bool
     */
    public function findOneWhere($conditions, $columns = ['*']) {
        $results = $this->findWhere($conditions, $columns);
        return !empty($results) ? $results[0] : false;
    }
    
    /**
     * Tạo bản ghi mới
     * 
     * @param array $data Dữ liệu bản ghi
     * @return string|bool ID của bản ghi mới hoặc false nếu thất bại
     */
    public function create($data) {
        if (!isset($data[$this->primaryKey])) {
            // Tạo UUID mới
            $uuid = bin2hex(random_bytes(16));
            $uuid = sprintf(
                '%08s-%04s-%04s-%04s-%012s',
                substr($uuid, 0, 8),
                substr($uuid, 8, 4),
                substr($uuid, 12, 4),
                substr($uuid, 16, 4),
                substr($uuid, 20, 12)
            );
            $data[$this->primaryKey] = $uuid;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($data as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        if ($stmt->execute()) {
            return $data[$this->primaryKey];
        }
        
        return false;
    }
    
    /**
     * Cập nhật bản ghi
     * 
     * @param string $id UUID của bản ghi
     * @param array $data Dữ liệu cần cập nhật
     * @return bool
     */
    public function update($id, $data) {
        $setStr = [];
        foreach (array_keys($data) as $column) {
            $setStr[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setStr) . " WHERE {$this->primaryKey} = :id";
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(':id', $id);
        foreach ($data as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Xóa bản ghi
     * 
     * @param string $id UUID của bản ghi
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
    
    /**
     * Đếm số bản ghi
     * 
     * @param array $conditions Điều kiện (tùy chọn)
     * @return int
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereStr = [];
            foreach (array_keys($conditions) as $column) {
                $whereStr[] = "{$column} = :{$column}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereStr);
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $stmt->bindValue(":{$column}", $value);
            }
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
} 