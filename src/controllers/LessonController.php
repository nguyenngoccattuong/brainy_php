<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\LessonService;
use App\Middleware\AuthMiddleware;

/**
 * Class LessonController
 * Controller xử lý các request liên quan đến bài học
 */
class LessonController {
    private $lessonService;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->lessonService = new \App\Services\LessonService($db->connect());
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy tất cả bài học
     * 
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getAll($data = []) {
        try {
            $lessons = $this->lessonService->getAllLessons();
            return ['lessons' => $lessons];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy thông tin bài học theo ID
     * 
     * @param string $id UUID của bài học
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getById($id, $data = []) {
        try {
            $lesson = $this->lessonService->getLessonById($id);
            
            if (!$lesson) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy bài học'];
            }
            
            return ['lesson' => $lesson];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy danh sách bài học theo danh mục
     * 
     * @param string $categoryId UUID của danh mục
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getByCategoryId($categoryId, $data = []) {
        try {
            $lessons = $this->lessonService->getLessonsByCategoryId($categoryId);
            return ['lessons' => $lessons];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Tạo bài học mới
     * 
     * @param array $data Dữ liệu bài học
     * @return array
     */
    public function create($data) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['title']) || empty($data['title'])) {
            http_response_code(400);
            return ['error' => 'Tiêu đề là bắt buộc'];
        }
        
        if (!isset($data['category_id']) || empty($data['category_id'])) {
            http_response_code(400);
            return ['error' => 'Danh mục là bắt buộc'];
        }
        
        try {
            $newLesson = $this->lessonService->createLesson($data);
            
            if (!$newLesson) {
                http_response_code(500);
                return ['error' => 'Không thể tạo bài học mới'];
            }
            
            http_response_code(201);
            return ['lesson' => $newLesson];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật thông tin bài học
     * 
     * @param string $id UUID của bài học
     * @param array $data Dữ liệu cần cập nhật
     * @return array
     */
    public function update($id, $data) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Kiểm tra xem có dữ liệu để cập nhật không
        if (empty($data)) {
            http_response_code(400);
            return ['error' => 'Không có dữ liệu để cập nhật'];
        }
        
        try {
            $updatedLesson = $this->lessonService->updateLesson($id, $data);
            
            if (!$updatedLesson) {
                http_response_code(500);
                return ['error' => 'Không thể cập nhật thông tin bài học'];
            }
            
            return ['lesson' => $updatedLesson];
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Không tìm thấy bài học') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa bài học
     * 
     * @param string $id UUID của bài học
     * @param array $data Dữ liệu request
     * @return array
     */
    public function delete($id, $data = []) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        try {
            $deleted = $this->lessonService->deleteLesson($id);
            
            if (!$deleted) {
                http_response_code(500);
                return ['error' => 'Không thể xóa bài học'];
            }
            
            return ['success' => true, 'message' => 'Xóa bài học thành công'];
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Không tìm thấy bài học') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            
            return ['error' => $e->getMessage()];
        }
    }
} 