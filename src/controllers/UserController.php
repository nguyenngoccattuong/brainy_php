<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\UserService;
use App\Middleware\AuthMiddleware;

/**
 * Class UserController
 * Controller xử lý các request liên quan đến người dùng
 */
class UserController {
    private $userService;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->userService = new UserService($db->connect());
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy tất cả người dùng
     * 
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getAll($data = []) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Chỉ admin mới có quyền xem tất cả người dùng
        if (!isset($currentUser['status']) || $currentUser['status'] !== 'admin') {
            http_response_code(403);
            return ['error' => 'Không đủ quyền truy cập'];
        }
        
        try {
            $users = $this->userService->getAllUsers();
            return ['users' => $users];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy thông tin người dùng theo ID
     * 
     * @param string $id UUID của người dùng
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getById($id, $data = []) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Kiểm tra quyền truy cập (chỉ người dùng đó hoặc admin mới được xem)
        if ($currentUser['id'] !== $id && (!isset($currentUser['status']) || $currentUser['status'] !== 'admin')) {
            http_response_code(403);
            return ['error' => 'Không đủ quyền truy cập'];
        }
        
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy người dùng'];
            }
            
            return ['user' => $user];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Tạo người dùng mới
     * 
     * @param array $data Dữ liệu người dùng
     * @return array
     */
    public function create($data) {
        // Kiểm tra dữ liệu đầu vào
        $requiredFields = ['username', 'email', 'password', 'full_name'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                return ['error' => "Thiếu trường bắt buộc: {$field}"];
            }
        }
        
        try {
            $newUser = $this->userService->createUser($data);
            
            if (!$newUser) {
                http_response_code(500);
                return ['error' => 'Không thể tạo người dùng mới'];
            }
            
            http_response_code(201);
            return ['user' => $newUser];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật thông tin người dùng
     * 
     * @param string $id UUID của người dùng
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
        
        // Kiểm tra quyền truy cập (chỉ người dùng đó hoặc admin mới được cập nhật)
        if ($currentUser['id'] !== $id && (!isset($currentUser['status']) || $currentUser['status'] !== 'admin')) {
            http_response_code(403);
            return ['error' => 'Không đủ quyền truy cập'];
        }
        
        // Kiểm tra xem có dữ liệu để cập nhật không
        if (empty($data)) {
            http_response_code(400);
            return ['error' => 'Không có dữ liệu để cập nhật'];
        }
        
        try {
            $updatedUser = $this->userService->updateUser($id, $data);
            
            if (!$updatedUser) {
                http_response_code(500);
                return ['error' => 'Không thể cập nhật thông tin người dùng'];
            }
            
            return ['user' => $updatedUser];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa người dùng
     * 
     * @param string $id UUID của người dùng
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
        
        // Chỉ admin mới có quyền xóa người dùng
        if (!isset($currentUser['status']) || $currentUser['status'] !== 'admin') {
            http_response_code(403);
            return ['error' => 'Không đủ quyền truy cập'];
        }
        
        try {
            $deleted = $this->userService->deleteUser($id);
            
            if (!$deleted) {
                http_response_code(500);
                return ['error' => 'Không thể xóa người dùng'];
            }
            
            return ['success' => true, 'message' => 'Xóa người dùng thành công'];
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Không tìm thấy người dùng') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            
            return ['error' => $e->getMessage()];
        }
    }
} 