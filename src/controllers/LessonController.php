<?php
namespace App\Controllers;

use App\Services\LessonService;
use App\Middleware\AuthMiddleware;

class LessonController {
    private $lessonService;
    private $authMiddleware;
    
    public function __construct() {
        $this->lessonService = new LessonService();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy danh sách tất cả lessons
     */
    public function getAll() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $lessons = $this->lessonService->getAllLessons();
            return ['lessons' => $lessons];
        } catch (\Exception $e) {
            error_log("GetAll Lessons Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách lessons'];
        }
    }
    
    /**
     * Lấy lesson theo ID
     */
    public function getById($lessonId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $lesson = $this->lessonService->getLessonById($lessonId);
            return ['lesson' => $lesson];
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Không tìm thấy lesson') {
                http_response_code(404);
            } else {
                http_response_code(500);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy lessons theo category_id
     */
    public function getByCategoryId($categoryId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $lessons = $this->lessonService->getLessonsByCategoryId($categoryId);
            return ['lessons' => $lessons];
        } catch (\Exception $e) {
            error_log("GetByCategoryId Lessons Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách lessons'];
        }
    }
    
    /**
     * Tạo lesson mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        // Khi nhận data từ form-data
        $title = isset($data['title']) ? $data['title'] : (isset($_POST['title']) ? $_POST['title'] : null);
        $categoryId = isset($data['category_id']) ? $data['category_id'] : (isset($_POST['category_id']) ? $_POST['category_id'] : null);
        $description = isset($data['description']) ? $data['description'] : (isset($_POST['description']) ? $_POST['description'] : null);
        $status = isset($data['status']) ? $data['status'] : (isset($_POST['status']) ? $_POST['status'] : 'active');
        
        // Kiểm tra dữ liệu đầu vào
        if (!$title) {
            http_response_code(400);
            return ['error' => 'Tiêu đề là bắt buộc'];
        }
        
        if (!$categoryId) {
            http_response_code(400);
            return ['error' => 'Category ID là bắt buộc'];
        }
        
        // Kiểm tra và đọc file markdown từ request
        $markdownContent = null;
        
        // Kiểm tra xem có file md được upload không
        if (isset($_FILES['markdown_file']) && $_FILES['markdown_file']['error'] === UPLOAD_ERR_OK) {
            // Đọc nội dung file markdown
            $markdownContent = file_get_contents($_FILES['markdown_file']['tmp_name']);
        } 
        // Kiểm tra xem content có được gửi dưới dạng field không
        else if (isset($data['content']) && !empty($data['content'])) {
            $markdownContent = $data['content'];
        }
        // Kiểm tra từ form data
        else if (isset($_POST['content']) && !empty($_POST['content'])) {
            $markdownContent = $_POST['content'];
        }
        
        if (!$markdownContent) {
            http_response_code(400);
            return ['error' => 'Nội dung markdown là bắt buộc (gửi dưới dạng file "markdown_file" hoặc field "content")'];
        }
        
        // Chuẩn bị dữ liệu để tạo lesson
        $lessonData = [
            'title' => $title,
            'category_id' => $categoryId,
            'description' => $description,
            'status' => $status,
            'content' => $markdownContent
        ];
        
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Lesson data prepared: " . json_encode(array_keys($lessonData)));
        }
        
        try {
            $result = $this->lessonService->createLesson($lessonData);
            
            http_response_code(201);
            return [
                'message' => 'Tạo lesson thành công',
                'lesson' => $result['lesson'],
                'markdown_file' => $result['markdown_file']
            ];
        } catch (\Exception $e) {
            error_log("Create Lesson Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật lesson
     */
    public function update($lessonId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $lesson = $this->lessonService->updateLesson($lessonId, $data);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'lesson' => $lesson
            ];
        } catch (\Exception $e) {
            error_log("Update Lesson Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy lesson') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa lesson
     */
    public function delete($lessonId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $this->lessonService->deleteLesson($lessonId);
            return ['message' => 'Xóa lesson thành công'];
        } catch (\Exception $e) {
            error_log("Delete Lesson Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy lesson') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }
} 