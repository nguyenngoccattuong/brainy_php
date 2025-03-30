<?php
namespace App\Models;

class CategoryModel extends Model {
    protected $table = 'categories';
    
    /**
     * Lấy danh sách tất cả categories
     */
    public function getAll() {
        $sql = "SELECT c.*, cf.file_url as image_url 
                FROM {$this->table} c 
                LEFT JOIN cloudinary_files cf ON c.image_id = cf.id 
                ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy category theo ID
     */
    public function getById($id) {
        $sql = "SELECT c.*, cf.file_url as image_url 
                FROM {$this->table} c 
                LEFT JOIN cloudinary_files cf ON c.image_id = cf.id 
                WHERE c.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Tạo category mới
     */
    public function create($data) {
        return $this->insert($data);
    }
    
    /**
     * Cập nhật category
     */
    public function update($id, $data) {
        return $this->updateById($id, $data);
    }
    
    /**
     * Xóa category
     */
    public function delete($id) {
        return $this->deleteById($id);
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