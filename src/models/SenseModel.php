<?php
namespace App\Models;

class SenseModel extends Model {
    protected $table = 'senses';
    
    /**
     * Lấy danh sách tất cả senses
     */
    public function getAll() {
        $sql = "SELECT s.*, w.word, w.phonetic, w.phonetic_text 
                FROM {$this->table} s 
                LEFT JOIN words w ON s.word_id = w.id 
                ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy sense theo ID
     */
    public function getById($id) {
        $sql = "SELECT s.*, w.word, w.phonetic, w.phonetic_text 
                FROM {$this->table} s 
                LEFT JOIN words w ON s.word_id = w.id 
                WHERE s.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy senses theo word_id
     */
    public function getByWordId($wordId) {
        $sql = "SELECT s.*, 
                GROUP_CONCAT(e.cf) as examples_cf,
                GROUP_CONCAT(e.x) as examples_x
                FROM {$this->table} s 
                LEFT JOIN examples e ON s.id = e.sense_id 
                WHERE s.word_id = :word_id 
                GROUP BY s.id 
                ORDER BY s.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':word_id', $wordId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo sense mới
     */
    public function create($data) {
        return parent::create($data);
    }
    
    /**
     * Cập nhật sense
     */
    public function update($id, $data) {
        return parent::update($id, $data);
    }
    
    /**
     * Xóa sense
     */
    public function delete($id) {
        return parent::delete($id);
    }
} 