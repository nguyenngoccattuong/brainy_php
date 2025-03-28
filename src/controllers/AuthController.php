<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\UserService;

/**
 * Class AuthController
 * Controller xử lý đăng nhập và đăng ký
 */
class AuthController {
    private $userService;
    
    public function __construct() {
        $db = new Database();
        $this->userService = new UserService($db->connect());
    }
    
    /**
     * Đăng ký người dùng mới
     * 
     * @param array $data Dữ liệu đăng ký
     * @return array
     */
    public function register($data) {
        // Kiểm tra dữ liệu đầu vào
        $requiredFields = ['username', 'email', 'password', 'full_name'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                return ['error' => "Thiếu trường bắt buộc: {$field}"];
            }
        }
        
        // Kiểm tra định dạng email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return ['error' => 'Email không hợp lệ'];
        }
        
        // Kiểm tra độ dài mật khẩu
        if (strlen($data['password']) < 6) {
            http_response_code(400);
            return ['error' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }
        
        try {
            // Tạo người dùng mới
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'full_name' => $data['full_name'],
                'status' => 'active'
            ];
            
            if (isset($data['avatar_url'])) {
                $userData['avatar_url'] = $data['avatar_url'];
            }
            
            $user = $this->userService->createUser($userData);
            
            if (!$user) {
                http_response_code(500);
                return ['error' => 'Không thể tạo tài khoản'];
            }
            
            // Tạo JWT token
            $token = $this->userService->generateToken($user);
            
            http_response_code(201);
            return [
                'message' => 'Đăng ký thành công',
                'user' => $user,
                'token' => $token
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Đăng nhập
     * 
     * @param array $data Dữ liệu đăng nhập
     * @return array
     */
    public function login($data) {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['username']) || empty($data['username']) || 
            !isset($data['password']) || empty($data['password'])) {
            http_response_code(400);
            return ['error' => 'Username/email và mật khẩu là bắt buộc'];
        }
        
        try {
            // Xác thực người dùng
            $user = $this->userService->authenticate($data['username'], $data['password']);
            
            if (!$user) {
                http_response_code(401);
                return ['error' => 'Username/email hoặc mật khẩu không chính xác'];
            }
            
            // Kiểm tra trạng thái tài khoản
            if ($user['status'] !== 'active') {
                http_response_code(403);
                return ['error' => 'Tài khoản đã bị khóa'];
            }
            
            // Tạo JWT token
            $token = $this->userService->generateToken($user);
            
            return [
                'message' => 'Đăng nhập thành công',
                'user' => $user,
                'token' => $token
            ];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
} 