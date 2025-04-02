<?php
namespace App\Services;

use App\Config\Database;
use App\Models\LessonModel;
use App\Models\CategoryModel;
use App\Controllers\CloudinaryController;

class LessonService {
    private $lessonModel;
    private $categoryModel;
    private $cloudinaryController;
    
    public function __construct() {
        $db = new Database();
        $connection = $db->connect();
        $this->lessonModel = new LessonModel($connection);
        $this->categoryModel = new CategoryModel($connection);
        $this->cloudinaryController = new CloudinaryController();
    }
    
    /**
     * Lấy tất cả lessons
     */
    public function getAllLessons() {
        return $this->lessonModel->getAll();
    }
    
    /**
     * Lấy lesson theo ID
     */
    public function getLessonById($lessonId) {
        $lesson = $this->lessonModel->getById($lessonId);
        if (!$lesson) {
            throw new \Exception('Không tìm thấy lesson');
        }
        return $lesson;
    }
    
    /**
     * Lấy lessons theo category ID
     */
    public function getLessonsByCategoryId($categoryId) {
        return $this->lessonModel->getByCategoryId($categoryId);
    }
    
    /**
     * Tạo lesson mới
     */
    public function createLesson($data) {
        // Kiểm tra category tồn tại
        $category = $this->categoryModel->getById($data['category_id']);
        if (!$category) {
            throw new \Exception('Category không tồn tại');
        }

        // Lấy order_index lớn nhất của category
        $maxOrder = $this->lessonModel->getMaxOrderIndex($data['category_id']);
        $data['order_index'] = $maxOrder + 1;

        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Creating lesson with data: " . json_encode($data));
        }

        // Tạo lesson
        $lessonId = $this->lessonModel->create($data);
        if (!$lessonId) {
            throw new \Exception('Không thể tạo lesson trong database');
        }

        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Lesson created with ID: " . $lessonId);
        }

        // Upload file markdown lên Cloudinary
        $uploadResult = $this->cloudinaryController->uploadLessonMarkdown([
            'content' => $data['content'],
            'lesson_id' => $lessonId
        ]);

        if (isset($uploadResult['error'])) {
            // Nếu upload thất bại, xóa lesson đã tạo
            $this->lessonModel->delete($lessonId);
            throw new \Exception('Không thể upload file markdown: ' . $uploadResult['error']);
        }

        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Markdown uploaded successfully: " . json_encode($uploadResult));
        }

        // Cập nhật lesson với cloudinary_file_id
        $this->lessonModel->update($lessonId, [
            'cloudinary_file_id' => $uploadResult['file']['id']
        ]);

        // Lấy và trả về lesson đã tạo
        $lesson = $this->lessonModel->getById($lessonId);
        return [
            'lesson' => $lesson,
            'markdown_file' => $uploadResult['file']
        ];
    }
    
    /**
     * Cập nhật lesson
     */
    public function updateLesson($lessonId, $data) {
        // Kiểm tra lesson tồn tại
        $lesson = $this->lessonModel->getById($lessonId);
        if (!$lesson) {
            throw new \Exception('Không tìm thấy lesson');
        }

        // Cập nhật thông tin
        $updated = $this->lessonModel->update($lessonId, $data);
        if (!$updated) {
            throw new \Exception('Không thể cập nhật thông tin');
        }

        return $this->lessonModel->getById($lessonId);
    }
    
    /**
     * Xóa lesson
     */
    public function deleteLesson($lessonId) {
        // Kiểm tra lesson tồn tại
        $lesson = $this->lessonModel->getById($lessonId);
        if (!$lesson) {
            throw new \Exception('Không tìm thấy lesson');
        }

        // Xóa lesson
        $deleted = $this->lessonModel->delete($lessonId);
        if (!$deleted) {
            throw new \Exception('Không thể xóa lesson');
        }

        return true;
    }
} 