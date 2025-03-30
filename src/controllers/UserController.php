<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\UserModel;
use App\Middleware\AuthMiddleware;

class UserController {
    private $userModel;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->userModel = new UserModel($db->connect());
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy danh sách tất cả người dùng
     */
    public function getAll() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $sql = "SELECT id, username, email, full_name, avatar_url, status, created_at, updated_at 
                    FROM users ORDER BY created_at DESC";
            $stmt = $this->userModel->getConnection()->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            return ['users' => $users];
        } catch (\Exception $e) {
            error_log("GetAll Users Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách người dùng'];
        }
    }
    
    /**
     * Lấy thông tin người dùng theo ID
     */
    public function getById($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $user = $this->userModel->getById($userId);
            
            if (!$user) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy người dùng'];
            }
            
            // Loại bỏ thông tin nhạy cảm
            unset($user['password']);
            
            return ['user' => $user];
        } catch (\Exception $e) {
            error_log("GetById User Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy thông tin người dùng'];
        }
    }
    
    /**
     * Tạo người dùng mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        // Kiểm tra dữ liệu đầu vào
        $requiredFields = ['username', 'email', 'password', 'full_name'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                return ['error' => "Thiếu trường bắt buộc: {$field}"];
            }
        }
        
        try {
            $userId = $this->userModel->register($data);
            
            if (!$userId) {
                http_response_code(400);
                return ['error' => 'Không thể tạo người dùng'];
            }
            
            $user = $this->userModel->getById($userId);
            unset($user['password']);
            
            http_response_code(201);
            return [
                'message' => 'Tạo người dùng thành công',
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log("Create User Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể tạo người dùng'];
        }
    }
    
    /**
     * Cập nhật thông tin người dùng
     */
    public function update($userId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra người dùng tồn tại
            $user = $this->userModel->getById($userId);
            if (!$user) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy người dùng'];
            }
            
            // Cập nhật thông tin
            $updated = $this->userModel->updateUser($userId, $data);
            
            if (!$updated) {
                http_response_code(400);
                return ['error' => 'Không thể cập nhật thông tin'];
            }
            
            $user = $this->userModel->getById($userId);
            unset($user['password']);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'user' => $user
            ];
        } catch (\Exception $e) {
            error_log("Update User Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể cập nhật thông tin'];
        }
    }
    
    /**
     * Xóa người dùng
     */
    public function delete($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra người dùng tồn tại
            $user = $this->userModel->getById($userId);
            if (!$user) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy người dùng'];
            }
            
            // Xóa người dùng
            $deleted = $this->userModel->delete($userId);
            
            if (!$deleted) {
                http_response_code(400);
                return ['error' => 'Không thể xóa người dùng'];
            }
            
            return ['message' => 'Xóa người dùng thành công'];
        } catch (\Exception $e) {
            error_log("Delete User Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể xóa người dùng'];
        }
    }
} 