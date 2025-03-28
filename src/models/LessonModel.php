<?php
namespace App\Models;

/**
 * Class LessonModel
 * Model xử lý dữ liệu bảng lessons
 */
class LessonModel extends Model {
    protected $table = 'lessons';
    
    /**
     * Tìm bài học theo tiêu đề
     * 
     * @param string $title Tiêu đề bài học
     * @return array|bool
     */
    public function findByTitle($title) {
        return $this->findOneWhere(['title' => $title]);
    }
    
    /**
     * Lấy danh sách bài học theo danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @param string $orderBy Sắp xếp theo cột
     * @param string $direction Hướng sắp xếp (ASC/DESC)
     * @return array
     */
    public function getByCategoryId($categoryId, $orderBy = 'order_index', $direction = 'ASC') {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = :category_id ORDER BY {$orderBy} {$direction}";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy danh sách từ vựng trong bài học
     * 
     * @param string $lessonId UUID của bài học
     * @return array
     */
    public function getWords($lessonId) {
        $sql = "SELECT * FROM words WHERE lesson_id = :lesson_id ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lesson_id', $lessonId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Cập nhật thứ tự bài học
     * 
     * @param string $lessonId UUID của bài học
     * @param int $orderIndex Thứ tự mới
     * @return bool
     */
    public function updateOrder($lessonId, $orderIndex) {
        $sql = "UPDATE {$this->table} SET order_index = :order_index WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $lessonId);
        $stmt->bindParam(':order_index', $orderIndex, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy thứ tự cao nhất trong danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @return int
     */
    public function getMaxOrderIndex($categoryId) {
        $sql = "SELECT MAX(order_index) as max_order FROM {$this->table} WHERE category_id = :category_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int) ($result['max_order'] ?? 0);
    }
    
    /**
     * Đếm số từ vựng trong bài học
     * 
     * @param string $lessonId UUID của bài học
     * @return int
     */
    public function countWords($lessonId) {
        $sql = "SELECT COUNT(*) as count FROM words WHERE lesson_id = :lesson_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lesson_id', $lessonId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }
    
    /**
     * Xóa bài học và tất cả từ vựng liên quan
     * 
     * @param string $lessonId UUID của bài học
     * @return bool
     */
    public function deleteWithWords($lessonId) {
        try {
            $this->conn->beginTransaction();
            
            // Lấy danh sách từ vựng
            $sql = "SELECT id FROM words WHERE lesson_id = :lesson_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':lesson_id', $lessonId);
            $stmt->execute();
            $words = $stmt->fetchAll();
            
            // Xóa từng từ vựng và dữ liệu liên quan
            $wordModel = new WordModel($this->conn);
            foreach ($words as $word) {
                $wordModel->deleteWordWithDetails($word['id']);
            }
            
            // Xóa bài học
            $result = $this->delete($lessonId);
            
            $this->conn->commit();
            return $result;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
} 