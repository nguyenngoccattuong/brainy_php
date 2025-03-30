<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\AuthService;

/**
 * Class AuthController
 * Controller xử lý đăng nhập, đăng ký và quản lý tài khoản
 */
class AuthController {
    private $authService;
    
    public function __construct() {
        $db = new Database();
        $this->authService = new AuthService($db->connect());
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
            // Tạo người dùng mới với AuthService
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
            
            $result = $this->authService->register($userData);
            
            http_response_code(201);
            return [
                'message' => 'Đăng ký thành công',
                'user' => $result['user'],
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'expires_in' => $result['expires_in']
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
            // Xác thực người dùng với AuthService
            $result = $this->authService->login($data['username'], $data['password']);
            
            return [
                'message' => 'Đăng nhập thành công',
                'user' => $result['user'],
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'expires_in' => $result['expires_in']
            ];
        } catch (\Exception $e) {
            http_response_code($e->getMessage() === 'Username/email hoặc mật khẩu không chính xác' ? 401 : 500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Đăng xuất
     * 
     * @param array $data Dữ liệu đăng xuất
     * @return array
     */
    public function logout($data) {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['refresh_token']) || empty($data['refresh_token'])) {
            http_response_code(400);
            return ['error' => 'Refresh token là bắt buộc'];
        }
        
        try {
            // Đăng xuất với AuthService
            $result = $this->authService->logout($data['refresh_token']);
            
            if (!$result) {
                http_response_code(400);
                return ['error' => 'Không thể đăng xuất'];
            }
            
            return ['message' => 'Đăng xuất thành công'];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Đăng xuất khỏi tất cả thiết bị
     * 
     * @param string $userId UUID của người dùng
     * @param array $data Dữ liệu đăng xuất
     * @return array
     */
    public function logoutAll($userId, $data) {
        try {
            // Đăng xuất khỏi tất cả thiết bị với AuthService
            $result = $this->authService->logoutAll($userId);
            
            if (!$result) {
                http_response_code(400);
                return ['error' => 'Không thể đăng xuất'];
            }
            
            return ['message' => 'Đăng xuất thành công khỏi tất cả thiết bị'];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Refresh token
     * 
     * @param array $data Dữ liệu refresh token
     * @return array
     */
    public function refreshToken($data) {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['refresh_token']) || empty($data['refresh_token'])) {
            http_response_code(400);
            return ['error' => 'Refresh token là bắt buộc'];
        }
        
        try {
            // Refresh token với AuthService
            $result = $this->authService->refreshToken($data['refresh_token']);
            
            return [
                'message' => 'Refresh token thành công',
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'expires_in' => $result['expires_in']
            ];
        } catch (\Exception $e) {
            http_response_code(401);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Quên mật khẩu
     * 
     * @param array $data Dữ liệu quên mật khẩu
     * @return array
     */
    public function forgotPassword($data) {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['email']) || empty($data['email'])) {
            http_response_code(400);
            return ['error' => 'Email là bắt buộc'];
        }
        
        // Kiểm tra định dạng email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return ['error' => 'Email không hợp lệ'];
        }
        
        try {
            // Yêu cầu reset mật khẩu với AuthService
            $result = $this->authService->forgotPassword($data['email']);
            
            return [
                'message' => $result['message'],
                // Trong môi trường phát triển, trả về token để test
                // Trong môi trường production, không nên trả về token
                'token' => $_ENV['DEBUG_MODE'] === 'true' ? $result['token'] : null,
                'expires_at' => $_ENV['DEBUG_MODE'] === 'true' ? $result['expires_at'] : null
            ];
        } catch (\Exception $e) {
            http_response_code(404);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xác thực token reset mật khẩu
     * 
     * @param string $token Token reset
     * @param array $data Dữ liệu không sử dụng
     * @return array
     */
    public function validateResetToken($token, $data) {
        try {
            // Xác thực token reset mật khẩu với AuthService
            $result = $this->authService->validateResetToken($token);
            
            return [
                'valid' => $result['valid'],
                'expires_at' => $result['expires_at']
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage(), 'valid' => false];
        }
    }
    
    /**
     * Reset mật khẩu
     * 
     * @param string $token Token reset
     * @param array $data Dữ liệu reset mật khẩu
     * @return array
     */
    public function resetPassword($token, $data) {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['password']) || empty($data['password'])) {
            http_response_code(400);
            return ['error' => 'Mật khẩu mới là bắt buộc'];
        }
        
        // Kiểm tra độ dài mật khẩu
        if (strlen($data['password']) < 6) {
            http_response_code(400);
            return ['error' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }
        
        try {
            // Reset mật khẩu với AuthService
            $result = $this->authService->resetPassword($token, $data['password']);
            
            return [
                'message' => $result['message'],
                'user' => $result['user']
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Thay đổi mật khẩu
     * 
     * @param string $userId UUID của người dùng
     * @param array $data Dữ liệu thay đổi mật khẩu
     * @return array
     */
    public function changePassword($userId, $data) {
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['current_password']) || empty($data['current_password']) ||
            !isset($data['new_password']) || empty($data['new_password'])) {
            http_response_code(400);
            return ['error' => 'Mật khẩu hiện tại và mật khẩu mới là bắt buộc'];
        }
        
        // Kiểm tra độ dài mật khẩu mới
        if (strlen($data['new_password']) < 6) {
            http_response_code(400);
            return ['error' => 'Mật khẩu mới phải có ít nhất 6 ký tự'];
        }
        
        try {
            // Thay đổi mật khẩu với AuthService
            $result = $this->authService->changePassword(
                $userId,
                $data['current_password'],
                $data['new_password']
            );
            
            return [
                'message' => $result['message'],
                'user' => $result['user']
            ];
        } catch (\Exception $e) {
            $code = $e->getMessage() === 'Mật khẩu hiện tại không chính xác' ? 401 : 400;
            http_response_code($code);
            return ['error' => $e->getMessage()];
        }
    }
} 