<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\LessonModel;
use App\Middleware\AuthMiddleware;

class LessonController {
    private $lessonModel;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->lessonModel = new LessonModel($db->connect());
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
            $lessons = $this->lessonModel->getAll();
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
            $lesson = $this->lessonModel->getById($lessonId);
            
            if (!$lesson) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy lesson'];
            }
            
            return ['lesson' => $lesson];
        } catch (\Exception $e) {
            error_log("GetById Lesson Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy thông tin lesson'];
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
            $lessons = $this->lessonModel->getByCategoryId($categoryId);
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
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['title']) || empty($data['title'])) {
            http_response_code(400);
            return ['error' => 'Tiêu đề là bắt buộc'];
        }
        
        if (!isset($data['category_id']) || empty($data['category_id'])) {
            http_response_code(400);
            return ['error' => 'Category ID là bắt buộc'];
        }
        
        try {
            $lessonId = $this->lessonModel->create($data);
            
            if (!$lessonId) {
                http_response_code(400);
                return ['error' => 'Không thể tạo lesson'];
            }
            
            $lesson = $this->lessonModel->getById($lessonId);
            
            http_response_code(201);
            return [
                'message' => 'Tạo lesson thành công',
                'lesson' => $lesson
            ];
        } catch (\Exception $e) {
            error_log("Create Lesson Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể tạo lesson'];
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
            // Kiểm tra lesson tồn tại
            $lesson = $this->lessonModel->getById($lessonId);
            if (!$lesson) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy lesson'];
            }
            
            // Cập nhật thông tin
            $updated = $this->lessonModel->update($lessonId, $data);
            
            if (!$updated) {
                http_response_code(400);
                return ['error' => 'Không thể cập nhật thông tin'];
            }
            
            $lesson = $this->lessonModel->getById($lessonId);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'lesson' => $lesson
            ];
        } catch (\Exception $e) {
            error_log("Update Lesson Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể cập nhật thông tin'];
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
            // Kiểm tra lesson tồn tại
            $lesson = $this->lessonModel->getById($lessonId);
            if (!$lesson) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy lesson'];
            }
            
            // Xóa lesson
            $deleted = $this->lessonModel->delete($lessonId);
            
            if (!$deleted) {
                http_response_code(400);
                return ['error' => 'Không thể xóa lesson'];
            }
            
            return ['message' => 'Xóa lesson thành công'];
        } catch (\Exception $e) {
            error_log("Delete Lesson Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể xóa lesson'];
        }
    }
} 