<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\AuthService;
use App\Utils\Response;

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
                return Response::error("Thiếu trường bắt buộc: {$field}", 400);
            }
        }
        
        // Kiểm tra định dạng email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Response::error('Email không hợp lệ', 400);
        }
        
        // Kiểm tra độ dài mật khẩu
        if (strlen($data['password']) < 6) {
            return Response::error('Mật khẩu phải có ít nhất 6 ký tự', 400);
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
            
            return Response::created($result, 'Đăng ký thành công');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 400);
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
            return Response::error('Username/email và mật khẩu là bắt buộc', 400);
        }
        
        try {
            // Xác thực người dùng với AuthService
            $result = $this->authService->login($data['username'], $data['password']);
            
            return Response::success($result, 'Đăng nhập thành công');
        } catch (\Exception $e) {
            $code = $e->getMessage() === 'Username/email hoặc mật khẩu không chính xác' ? 401 : 500;
            return Response::error($e->getMessage(), $code);
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
            return Response::error('Refresh token là bắt buộc', 400);
        }
        
        try {
            // Đăng xuất với AuthService
            $result = $this->authService->logout($data['refresh_token']);
            
            if (!$result) {
                return Response::error('Không thể đăng xuất', 400);
            }
            
            return Response::success(null, 'Đăng xuất thành công');
        } catch (\Exception $e) {
            return Response::serverError($e->getMessage());
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
                return Response::error('Không thể đăng xuất', 400);
            }
            
            return Response::success(null, 'Đăng xuất thành công khỏi tất cả thiết bị');
        } catch (\Exception $e) {
            return Response::serverError($e->getMessage());
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
            return Response::error('Refresh token là bắt buộc', 400);
        }
        
        try {
            // Refresh token với AuthService
            $result = $this->authService->refreshToken($data['refresh_token']);
            
            return Response::success($result, 'Refresh token thành công');
        } catch (\Exception $e) {
            return Response::unauthorized($e->getMessage());
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
            return Response::error('Email là bắt buộc', 400);
        }
        
        // Kiểm tra định dạng email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Response::error('Email không hợp lệ', 400);
        }
        
        try {
            // Yêu cầu reset mật khẩu với AuthService
            $result = $this->authService->forgotPassword($data['email']);
            
            $responseData = [];
            // Trong môi trường phát triển, trả về token để test
            if ($_ENV['DEBUG_MODE'] === 'true') {
                $responseData = [
                    'token' => $result['token'],
                    'expires_at' => $result['expires_at']
                ];
            }
            
            return Response::success($responseData, $result['message']);
        } catch (\Exception $e) {
            return Response::notFound($e->getMessage());
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
            
            return Response::success([
                'valid' => $result['valid'],
                'expires_at' => $result['expires_at']
            ]);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 400, ['valid' => false]);
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
            return Response::error('Mật khẩu mới là bắt buộc', 400);
        }
        
        // Kiểm tra độ dài mật khẩu
        if (strlen($data['password']) < 6) {
            return Response::error('Mật khẩu phải có ít nhất 6 ký tự', 400);
        }
        
        try {
            // Reset mật khẩu với AuthService
            $result = $this->authService->resetPassword($token, $data['password']);
            
            if (!$result) {
                return Response::error('Không thể đặt lại mật khẩu', 400);
            }
            
            return Response::success(null, 'Đặt lại mật khẩu thành công');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 400);
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
        $requiredFields = ['current_password', 'new_password'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return Response::error("Thiếu trường bắt buộc: {$field}", 400);
            }
        }
        
        // Kiểm tra độ dài mật khẩu mới
        if (strlen($data['new_password']) < 6) {
            return Response::error('Mật khẩu mới phải có ít nhất 6 ký tự', 400);
        }
        
        try {
            // Thay đổi mật khẩu với AuthService
            $result = $this->authService->changePassword($userId, $data['current_password'], $data['new_password']);
            
            if (!$result) {
                return Response::error('Không thể thay đổi mật khẩu', 400);
            }
            
            return Response::success(null, 'Thay đổi mật khẩu thành công');
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 400);
        }
    }
} 