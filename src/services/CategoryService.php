<?php
namespace App\Services;

use App\Models\CategoryModel;
use App\Models\LessonModel;
use App\Models\CloudinaryFileModel;

/**
 * Class CategoryService
 * Service xử lý logic nghiệp vụ liên quan đến danh mục
 */
class CategoryService {
    private $categoryModel;
    private $lessonModel;
    private $cloudinaryFileModel;
    
    /**
     * Constructor
     * 
     * @param \PDO $conn Kết nối PDO đến database
     */
    public function __construct($conn) {
        $this->categoryModel = new CategoryModel($conn);
        $this->lessonModel = new LessonModel($conn);
        $this->cloudinaryFileModel = new CloudinaryFileModel($conn);
    }
    
    /**
     * Lấy tất cả danh mục
     * 
     * @return array
     */
    public function getAllCategories() {
        $categories = $this->categoryModel->getAll();
        
        // Lấy thông tin hình ảnh cho mỗi danh mục
        foreach ($categories as &$category) {
            if (!empty($category['image_id'])) {
                $image = $this->cloudinaryFileModel->getById($category['image_id']);
                if ($image) {
                    $category['image_url'] = $image['file_url'];
                }
            }
        }
        
        return $categories;
    }
    
    /**
     * Lấy thông tin danh mục theo ID
     * 
     * @param string $id UUID của danh mục
     * @return array|bool
     */
    public function getCategoryById($id) {
        $category = $this->categoryModel->getById($id);
        
        if ($category) {
            // Lấy thông tin hình ảnh
            if (!empty($category['image_id'])) {
                $image = $this->cloudinaryFileModel->getById($category['image_id']);
                if ($image) {
                    $category['image_url'] = $image['file_url'];
                }
            }
            
            // Lấy danh sách bài học
            $category['lessons'] = $this->lessonModel->getByCategoryId($id);
        }
        
        return $category;
    }
    
    /**
     * Tạo danh mục mới
     * 
     * @param array $data Dữ liệu danh mục
     * @return array|bool Thông tin danh mục mới hoặc false nếu thất bại
     */
    public function createCategory($data) {
        // Kiểm tra xem title đã tồn tại chưa
        if ($this->categoryModel->findByTitle($data['title'])) {
            throw new \Exception('Tiêu đề danh mục đã tồn tại');
        }
        
        // Xử lý image_id nếu có
        if (isset($data['image_id'])) {
            $image = $this->cloudinaryFileModel->getById($data['image_id']);
            if (!$image) {
                throw new \Exception('Hình ảnh không tồn tại');
            }
        }
        
        // Mặc định progress và total
        if (!isset($data['progress'])) {
            $data['progress'] = 0;
        }
        
        if (!isset($data['total'])) {
            $data['total'] = 0;
        }
        
        // Tạo danh mục mới
        $categoryId = $this->categoryModel->create($data);
        
        if ($categoryId) {
            return $this->getCategoryById($categoryId);
        }
        
        return false;
    }
    
    /**
     * Cập nhật thông tin danh mục
     * 
     * @param string $id UUID của danh mục
     * @param array $data Dữ liệu cần cập nhật
     * @return array|bool Thông tin danh mục sau khi cập nhật hoặc false nếu thất bại
     */
    public function updateCategory($id, $data) {
        // Kiểm tra xem danh mục có tồn tại không
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            throw new \Exception('Không tìm thấy danh mục');
        }
        
        // Kiểm tra xem title mới đã tồn tại chưa (nếu có thay đổi)
        if (isset($data['title']) && $data['title'] !== $category['title']) {
            $existingCategory = $this->categoryModel->findByTitle($data['title']);
            if ($existingCategory) {
                throw new \Exception('Tiêu đề danh mục đã tồn tại');
            }
        }
        
        // Xử lý image_id nếu có
        if (isset($data['image_id']) && $data['image_id'] !== $category['image_id']) {
            $image = $this->cloudinaryFileModel->getById($data['image_id']);
            if (!$image) {
                throw new \Exception('Hình ảnh không tồn tại');
            }
        }
        
        // Cập nhật thông tin danh mục
        $updated = $this->categoryModel->update($id, $data);
        
        if ($updated) {
            return $this->getCategoryById($id);
        }
        
        return false;
    }
    
    /**
     * Xóa danh mục
     * 
     * @param string $id UUID của danh mục
     * @return bool
     */
    public function deleteCategory($id) {
        // Kiểm tra xem danh mục có tồn tại không
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            throw new \Exception('Không tìm thấy danh mục');
        }
        
        // Lấy danh sách bài học
        $lessons = $this->lessonModel->getByCategoryId($id);
        
        // Xóa từng bài học và từ vựng liên quan
        foreach ($lessons as $lesson) {
            $this->lessonModel->deleteWithWords($lesson['id']);
        }
        
        return $this->categoryModel->delete($id);
    }
    
    /**
     * Cập nhật tiến độ của danh mục
     * 
     * @param string $id UUID của danh mục
     * @param int $progress Tiến độ mới
     * @return bool
     */
    public function updateProgress($id, $progress) {
        // Kiểm tra xem danh mục có tồn tại không
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            throw new \Exception('Không tìm thấy danh mục');
        }
        
        // Kiểm tra giá trị progress
        if ($progress < 0 || $progress > $category['total']) {
            throw new \Exception('Giá trị tiến độ không hợp lệ');
        }
        
        return $this->categoryModel->updateProgress($id, $progress);
    }
    
    /**
     * Cập nhật tổng số từ vựng của danh mục
     * 
     * @param string $id UUID của danh mục
     * @return bool
     */
    public function updateTotal($id) {
        // Kiểm tra xem danh mục có tồn tại không
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            throw new \Exception('Không tìm thấy danh mục');
        }
        
        return $this->categoryModel->updateTotal($id);
    }
} 