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
     * Lấy danh sách tất cả categories kèm theo lessons
     */
    public function getAllWithLessons() {
        // First, get all categories
        $categories = $this->getAll();
        
        if (empty($categories)) {
            return [];
        }
        
        // For each category, fetch its lessons
        foreach ($categories as &$category) {
            $sql = "SELECT l.*, cf.file_url as image_url 
                    FROM lessons l 
                    LEFT JOIN cloudinary_files cf ON l.cloudinary_file_id = cf.id 
                    WHERE l.category_id = :category_id 
                    ORDER BY l.order_index ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':category_id', $category['id']);
            $stmt->execute();
            $category['lessons'] = $stmt->fetchAll();
        }
        
        return $categories;
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
     * Lấy category theo ID kèm theo lessons
     */
    public function getByIdWithLessons($id) {
        // Get the category
        $category = $this->getById($id);
        
        if (!$category) {
            return null;
        }
        
        // Fetch lessons for this category
        $sql = "SELECT l.*, cf.file_url as image_url 
                FROM lessons l 
                LEFT JOIN cloudinary_files cf ON l.cloudinary_file_id = cf.id 
                WHERE l.category_id = :category_id 
                ORDER BY l.order_index ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':category_id', $id);
        $stmt->execute();
        $category['lessons'] = $stmt->fetchAll();
        
        return $category;
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