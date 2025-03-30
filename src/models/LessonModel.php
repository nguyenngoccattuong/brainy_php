<?php
namespace App\Models;

class LessonModel extends Model {
    protected $table = 'lessons';
    
    /**
     * Lấy danh sách tất cả lessons
     */
    public function getAll() {
        $sql = "SELECT l.*, cf.file_url as image_url, c.title as category_title 
                FROM {$this->table} l 
                LEFT JOIN cloudinary_files cf ON l.cloudinary_file_id = cf.id 
                LEFT JOIN categories c ON l.category_id = c.id 
                ORDER BY l.order_index ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy lesson theo ID
     */
    public function getById($id) {
        $sql = "SELECT l.*, cf.file_url as image_url, c.title as category_title 
                FROM {$this->table} l 
                LEFT JOIN cloudinary_files cf ON l.cloudinary_file_id = cf.id 
                LEFT JOIN categories c ON l.category_id = c.id 
                WHERE l.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy lessons theo category_id
     */
    public function getByCategoryId($categoryId) {
        $sql = "SELECT l.*, cf.file_url as image_url 
                FROM {$this->table} l 
                LEFT JOIN cloudinary_files cf ON l.cloudinary_file_id = cf.id 
                WHERE l.category_id = :category_id 
                ORDER BY l.order_index ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo lesson mới
     */
    public function create($data) {
        return $this->insert($data);
    }
    
    /**
     * Cập nhật lesson
     */
    public function update($id, $data) {
        return $this->updateById($id, $data);
    }
    
    /**
     * Xóa lesson
     */
    public function delete($id) {
        return $this->deleteById($id);
    }
    
    /**
     * Cập nhật thứ tự của lesson
     */
    public function updateOrder($id, $orderIndex) {
        $sql = "UPDATE {$this->table} SET order_index = :order_index WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':order_index', $orderIndex);
        return $stmt->execute();
    }
} 