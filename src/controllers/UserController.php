<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

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
            return Response::unauthorized();
        }
        
        try {
            $users = $this->userService->getAllUsers();
            return Response::success(['users' => $users], 'Lấy danh sách users thành công');
        } catch (\Exception $e) {
            error_log("GetAll Users Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy danh sách users');
        }
    }
    
    /**
     * Lấy user theo ID
     */
    public function getById($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $user = $this->userService->getUserById($userId);
            return Response::success(['user' => $user], 'Lấy thông tin user thành công');
        } catch (\Exception $e) {
            error_log("GetById User Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy user') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::serverError($e->getMessage());
            }
        }
    }
    
    /**
     * Tạo user mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['username']) || empty($data['username'])) {
            return Response::error('Username là bắt buộc', 400);
        }
        if (!isset($data['email']) || empty($data['email'])) {
            return Response::error('Email là bắt buộc', 400);
        }
        if (!isset($data['password']) || empty($data['password'])) {
            return Response::error('Password là bắt buộc', 400);
        }

        try {
            $user = $this->userService->createUser($data);
            
            return Response::created(['user' => $user], 'Tạo user thành công');
        } catch (\Exception $e) {
            error_log("Create User Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Cập nhật user
     */
    public function update($userId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $user = $this->userService->updateUser($userId, $data);
            
            return Response::success(['user' => $user], 'Cập nhật thông tin thành công');
        } catch (\Exception $e) {
            error_log("Update User Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy user') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::error($e->getMessage(), 400);
            }
        }
    }
    
    /**
     * Xóa user
     */
    public function delete($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $this->userService->deleteUser($userId);
            return Response::success(null, 'Xóa user thành công');
        } catch (\Exception $e) {
            error_log("Delete User Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy user') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::error($e->getMessage(), 400);
            }
        }
    }

    /**
     * Lấy tiến độ học của user
     */
    public function getProgress($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $progress = $this->userService->getUserProgress($userId);
            return Response::success(['progress' => $progress], 'Lấy tiến độ học thành công');
        } catch (\Exception $e) {
            error_log("Get User Progress Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy tiến độ học');
        }
    }

    /**
     * Cập nhật tiến độ học
     */
    public function updateProgress($userId, $wordId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $progress = $this->userService->updateUserProgress($userId, $wordId, $data);
            return Response::success(['progress' => $progress], 'Cập nhật tiến độ thành công');
        } catch (\Exception $e) {
            error_log("Update User Progress Error: " . $e->getMessage());
            return Response::serverError('Không thể cập nhật tiến độ học');
        }
    }

    /**
     * Xóa tiến độ học của user
     */
    public function deleteProgress($userId, $wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $this->userService->deleteUserProgress($userId, $wordId);
            return Response::success(null, 'Xóa tiến độ học thành công');
        } catch (\Exception $e) {
            error_log("Delete User Progress Error: " . $e->getMessage());
            return Response::serverError('Không thể xóa tiến độ học');
        }
    }

    /**
     * Lấy ghi chú của user
     */
    public function getNotes($userId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $notes = $this->userService->getUserNotes($userId);
            return Response::success(['notes' => $notes], 'Lấy ghi chú thành công');
        } catch (\Exception $e) {
            error_log("Get User Notes Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy ghi chú');
        }
    }

    /**
     * Tạo/cập nhật ghi chú
     */
    public function saveNote($userId, $wordId, $note) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $result = $this->userService->saveUserNote($userId, $wordId, $note);
            return Response::success(['note' => $result], 'Lưu ghi chú thành công');
        } catch (\Exception $e) {
            error_log("Save User Note Error: " . $e->getMessage());
            return Response::serverError('Không thể lưu ghi chú');
        }
    }

    /**
     * Xóa ghi chú
     */
    public function deleteNote($userId, $wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $this->userService->deleteUserNote($userId, $wordId);
            return Response::success(null, 'Xóa ghi chú thành công');
        } catch (\Exception $e) {
            error_log("Delete User Note Error: " . $e->getMessage());
            return Response::serverError('Không thể xóa ghi chú');
        }
    }
} 