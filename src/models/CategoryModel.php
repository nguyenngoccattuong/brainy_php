<?php
namespace App\Models;

class CategoryModel extends Model {
    protected $table = 'categories';
    
    /**
     * Lấy danh sách tất cả categories
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy category theo ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Tạo category mới
     */
    public function create($data) {
        return parent::create($data);
    }
    
    /**
     * Cập nhật category
     */
    public function update($id, $data) {
        return parent::update($id, $data);
    }
    
    /**
     * Xóa category
     */
    public function delete($id) {
        return parent::delete($id);
    }
    
    /**
     * Cập nhật tiến độ của category
     */
    public function updateProgress($id) {
        $sql = "UPDATE {$this->table} 
                SET progress = (
                    SELECT COUNT(*) 
                    FROM lessons 
                    WHERE category_id = :id
                ) 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
} 