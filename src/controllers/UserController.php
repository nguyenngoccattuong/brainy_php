<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Middleware\AuthMiddleware;

class UserController {
    private $userService;
    private $authMiddleware;
    
    public function __construct() {
        $this->userService = new UserService();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy danh sách tất cả users
     */
    public function getAll() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $users = $this->userService->getAllUsers();
            return ['users' => $users];
        } catch (\Exception $e) {
            error_log("GetAll Users Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách users'];
        }
    }
    
    /**
     * Lấy user theo ID
     */
    public function getById($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $user = $this->userService->getUserById($userId);
            return ['user' => $user];
        } catch (\Exception $e) {
            error_log("GetById User Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy user') {
                http_response_code(404);
            } else {
                http_response_code(500);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Tạo user mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['username']) || empty($data['username'])) {
            http_response_code(400);
            return ['error' => 'Username là bắt buộc'];
        }
        if (!isset($data['email']) || empty($data['email'])) {
            http_response_code(400);
            return ['error' => 'Email là bắt buộc'];
        }
        if (!isset($data['password']) || empty($data['password'])) {
            http_response_code(400);
            return ['error' => 'Password là bắt buộc'];
        }

        try {
            $user = $this->userService->createUser($data);
            
            http_response_code(201);
            return [
                'message' => 'Tạo user thành công',
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log("Create User Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật user
     */
    public function update($userId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $user = $this->userService->updateUser($userId, $data);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log("Update User Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy user') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa user
     */
    public function delete($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $this->userService->deleteUser($userId);
            return ['message' => 'Xóa user thành công'];
        } catch (\Exception $e) {
            error_log("Delete User Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy user') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Lấy tiến độ học của user
     */
    public function getProgress($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $progress = $this->userService->getUserProgress($userId);
            return ['progress' => $progress];
        } catch (\Exception $e) {
            error_log("Get User Progress Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy tiến độ học'];
        }
    }

    /**
     * Cập nhật tiến độ học
     */
    public function updateProgress($userId, $wordId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $progress = $this->userService->updateUserProgress($userId, $wordId, $data);
            return [
                'message' => 'Cập nhật tiến độ thành công',
                'progress' => $progress
            ];
        } catch (\Exception $e) {
            error_log("Update User Progress Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể cập nhật tiến độ học'];
        }
    }

    /**
     * Lấy ghi chú của user
     */
    public function getNotes($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $notes = $this->userService->getUserNotes($userId);
            return ['notes' => $notes];
        } catch (\Exception $e) {
            error_log("Get User Notes Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy ghi chú'];
        }
    }

    /**
     * Tạo/cập nhật ghi chú
     */
    public function saveNote($userId, $wordId, $note) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $result = $this->userService->saveUserNote($userId, $wordId, $note);
            return [
                'message' => 'Lưu ghi chú thành công',
                'note' => $result
            ];
        } catch (\Exception $e) {
            error_log("Save User Note Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lưu ghi chú'];
        }
    }

    /**
     * Xóa ghi chú
     */
    public function deleteNote($userId, $wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $this->userService->deleteUserNote($userId, $wordId);
            return ['message' => 'Xóa ghi chú thành công'];
        } catch (\Exception $e) {
            error_log("Delete User Note Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể xóa ghi chú'];
        }
    }
} 