<?php
namespace App\Models;

/**
 * Class CategoryModel
 * Model xử lý dữ liệu bảng categories
 */
class CategoryModel extends Model {
    protected $table = 'categories';
    
    /**
     * Tìm danh mục theo tiêu đề
     * 
     * @param string $title Tiêu đề danh mục
     * @return array|bool
     */
    public function findByTitle($title) {
        return $this->findOneWhere(['title' => $title]);
    }
    
    /**
     * Lấy danh sách từ vựng trong danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @return array
     */
    public function getWords($categoryId) {
        $sql = "SELECT w.* FROM words w
                JOIN lessons l ON w.lesson_id = l.id
                WHERE l.category_id = :category_id
                ORDER BY w.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy danh sách bài học trong danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @param string $orderBy Sắp xếp theo cột
     * @param string $direction Hướng sắp xếp (ASC/DESC)
     * @return array
     */
    public function getLessons($categoryId, $orderBy = 'order_index', $direction = 'ASC') {
        $sql = "SELECT * FROM lessons 
                WHERE category_id = :category_id 
                ORDER BY {$orderBy} {$direction}";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Cập nhật tiến độ của danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @param int $progress Tiến độ mới
     * @return bool
     */
    public function updateProgress($categoryId, $progress) {
        $sql = "UPDATE {$this->table} SET progress = :progress WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $categoryId);
        $stmt->bindParam(':progress', $progress, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Tính toán tổng số từ vựng trong danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @return int
     */
    public function calculateTotal($categoryId) {
        $sql = "SELECT COUNT(w.id) as total FROM words w
                JOIN lessons l ON w.lesson_id = l.id
                WHERE l.category_id = :category_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int) $result['total'];
    }
    
    /**
     * Cập nhật tổng số từ vựng của danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @return bool
     */
    public function updateTotal($categoryId) {
        $total = $this->calculateTotal($categoryId);
        
        $sql = "UPDATE {$this->table} SET total = :total WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $categoryId);
        $stmt->bindParam(':total', $total, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
} 