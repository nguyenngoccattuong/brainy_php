<?php
namespace App\Models;

class ExampleModel extends Model {
    protected $table = 'examples';
    
    /**
     * Lấy danh sách tất cả examples
     */
    public function getAll() {
        $sql = "SELECT e.*, s.definition, w.word 
                FROM {$this->table} e 
                LEFT JOIN senses s ON e.sense_id = s.id 
                LEFT JOIN words w ON s.word_id = w.id 
                ORDER BY e.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy example theo ID
     */
    public function getById($id) {
        $sql = "SELECT e.*, s.definition, w.word 
                FROM {$this->table} e 
                LEFT JOIN senses s ON e.sense_id = s.id 
                LEFT JOIN words w ON s.word_id = w.id 
                WHERE e.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy examples theo sense_id
     */
    public function getBySenseId($senseId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE sense_id = :sense_id 
                ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':sense_id', $senseId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo example mới
     */
    public function create($data) {
        return parent::create($data);
    }
    
    /**
     * Cập nhật example
     */
    public function update($id, $data) {
        return parent::update($id, $data);
    }
    
    /**
     * Xóa example
     */
    public function delete($id) {
        return parent::delete($id);
    }
} 