<?php
namespace App\Services;

use App\Models\LessonModel;
use App\Models\CategoryModel;
use App\Models\WordModel;
use App\Models\CloudinaryFileModel;

/**
 * Class LessonService
 * Service xử lý logic nghiệp vụ liên quan đến bài học
 */
class LessonService {
    private $lessonModel;
    private $categoryModel;
    private $wordModel;
    private $cloudinaryFileModel;
    
    /**
     * Constructor
     * 
     * @param \PDO $conn Kết nối PDO đến database
     */
    public function __construct($conn) {
        $this->lessonModel = new LessonModel($conn);
        $this->categoryModel = new CategoryModel($conn);
        $this->wordModel = new WordModel($conn);
        $this->cloudinaryFileModel = new CloudinaryFileModel($conn);
    }
    
    /**
     * Lấy tất cả bài học
     * 
     * @return array
     */
    public function getAllLessons() {
        $lessons = $this->lessonModel->getAll(['*'], 'title', 'ASC');
        
        // Lấy thông tin hình ảnh cho mỗi bài học
        foreach ($lessons as &$lesson) {
            if (!empty($lesson['cloudinary_file_id'])) {
                $image = $this->cloudinaryFileModel->getById($lesson['cloudinary_file_id']);
                if ($image) {
                    $lesson['image_url'] = $image['file_url'];
                }
            }
            
            // Đếm số từ vựng trong bài học
            $lesson['word_count'] = $this->lessonModel->countWords($lesson['id']);
        }
        
        return $lessons;
    }
    
    /**
     * Lấy thông tin bài học theo ID
     * 
     * @param string $id UUID của bài học
     * @return array|bool
     */
    public function getLessonById($id) {
        $lesson = $this->lessonModel->getById($id);
        
        if ($lesson) {
            // Lấy thông tin hình ảnh
            if (!empty($lesson['cloudinary_file_id'])) {
                $image = $this->cloudinaryFileModel->getById($lesson['cloudinary_file_id']);
                if ($image) {
                    $lesson['image_url'] = $image['file_url'];
                }
            }
            
            // Lấy danh sách từ vựng
            $lesson['words'] = $this->lessonModel->getWords($id);
            
            // Lấy thông tin danh mục
            $category = $this->categoryModel->getById($lesson['category_id']);
            if ($category) {
                $lesson['category'] = [
                    'id' => $category['id'],
                    'title' => $category['title']
                ];
            }
        }
        
        return $lesson;
    }
    
    /**
     * Lấy danh sách bài học theo danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @return array
     */
    public function getLessonsByCategoryId($categoryId) {
        // Kiểm tra xem danh mục có tồn tại không
        $category = $this->categoryModel->getById($categoryId);
        if (!$category) {
            throw new \Exception('Không tìm thấy danh mục');
        }
        
        $lessons = $this->lessonModel->getByCategoryId($categoryId);
        
        // Lấy thông tin hình ảnh và đếm số từ vựng cho mỗi bài học
        foreach ($lessons as &$lesson) {
            if (!empty($lesson['cloudinary_file_id'])) {
                $image = $this->cloudinaryFileModel->getById($lesson['cloudinary_file_id']);
                if ($image) {
                    $lesson['image_url'] = $image['file_url'];
                }
            }
            
            // Đếm số từ vựng trong bài học
            $lesson['word_count'] = $this->lessonModel->countWords($lesson['id']);
        }
        
        return $lessons;
    }
    
    /**
     * Tạo bài học mới
     * 
     * @param array $data Dữ liệu bài học
     * @return array|bool Thông tin bài học mới hoặc false nếu thất bại
     */
    public function createLesson($data) {
        // Kiểm tra xem danh mục có tồn tại không
        $category = $this->categoryModel->getById($data['category_id']);
        if (!$category) {
            throw new \Exception('Danh mục không tồn tại');
        }
        
        // Xử lý cloudinary_file_id nếu có
        if (isset($data['cloudinary_file_id'])) {
            $image = $this->cloudinaryFileModel->getById($data['cloudinary_file_id']);
            if (!$image) {
                throw new \Exception('Hình ảnh không tồn tại');
            }
        }
        
        // Kiểm tra và thiết lập order_index
        if (!isset($data['order_index'])) {
            // Lấy order_index cao nhất trong danh mục và cộng 1
            $data['order_index'] = $this->lessonModel->getMaxOrderIndex($data['category_id']) + 1;
        }
        
        // Tạo bài học mới
        $lessonId = $this->lessonModel->create($data);
        
        if ($lessonId) {
            // Cập nhật tổng số từ vựng trong danh mục
            $this->categoryModel->updateTotal($data['category_id']);
            
            return $this->getLessonById($lessonId);
        }
        
        return false;
    }
    
    /**
     * Cập nhật thông tin bài học
     * 
     * @param string $id UUID của bài học
     * @param array $data Dữ liệu cần cập nhật
     * @return array|bool Thông tin bài học sau khi cập nhật hoặc false nếu thất bại
     */
    public function updateLesson($id, $data) {
        // Kiểm tra xem bài học có tồn tại không
        $lesson = $this->lessonModel->getById($id);
        if (!$lesson) {
            throw new \Exception('Không tìm thấy bài học');
        }
        
        // Kiểm tra xem danh mục có thay đổi không
        if (isset($data['category_id']) && $data['category_id'] !== $lesson['category_id']) {
            // Kiểm tra xem danh mục mới có tồn tại không
            $category = $this->categoryModel->getById($data['category_id']);
            if (!$category) {
                throw new \Exception('Danh mục không tồn tại');
            }
            
            // Cập nhật order_index cho bài học trong danh mục mới
            $data['order_index'] = $this->lessonModel->getMaxOrderIndex($data['category_id']) + 1;
            
            // Lưu lại danh mục cũ để cập nhật tổng số từ vựng sau
            $oldCategoryId = $lesson['category_id'];
        }
        
        // Xử lý cloudinary_file_id nếu có
        if (isset($data['cloudinary_file_id']) && $data['cloudinary_file_id'] !== $lesson['cloudinary_file_id']) {
            $image = $this->cloudinaryFileModel->getById($data['cloudinary_file_id']);
            if (!$image) {
                throw new \Exception('Hình ảnh không tồn tại');
            }
        }
        
        // Cập nhật thông tin bài học
        $updated = $this->lessonModel->update($id, $data);
        
        if ($updated) {
            // Cập nhật tổng số từ vựng trong danh mục
            if (isset($oldCategoryId)) {
                $this->categoryModel->updateTotal($oldCategoryId);
                $this->categoryModel->updateTotal($data['category_id']);
            }
            
            return $this->getLessonById($id);
        }
        
        return false;
    }
    
    /**
     * Xóa bài học
     * 
     * @param string $id UUID của bài học
     * @return bool
     */
    public function deleteLesson($id) {
        // Kiểm tra xem bài học có tồn tại không
        $lesson = $this->lessonModel->getById($id);
        if (!$lesson) {
            throw new \Exception('Không tìm thấy bài học');
        }
        
        // Lưu lại category_id để cập nhật tổng số từ vựng sau khi xóa
        $categoryId = $lesson['category_id'];
        
        // Xóa bài học và tất cả từ vựng liên quan
        $deleted = $this->lessonModel->deleteWithWords($id);
        
        if ($deleted) {
            // Cập nhật tổng số từ vựng trong danh mục
            $this->categoryModel->updateTotal($categoryId);
            
            return true;
        }
        
        return false;
    }
} 