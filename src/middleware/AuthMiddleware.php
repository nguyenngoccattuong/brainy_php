<?php
namespace App\Middleware;

use App\Config\Database;
use App\Services\AuthService;

/**
 * Class AuthMiddleware
 * Middleware xử lý xác thực JWT token
 */
class AuthMiddleware {
    private $authService;
    
    public function __construct() {
        $db = new Database();
        $this->authService = new AuthService($db->connect());
    }
    
    /**
     * Xác thực JWT token từ header Authorization
     * 
     * @return array|bool Thông tin người dùng nếu xác thực thành công, ngược lại false
     */
    public function authenticate() {
        // Lấy Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Auth header: " . $authHeader);
        }
        
        // Kiểm tra Bearer token
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("No Bearer token found in header");
            }
            return false;
        }
        
        // Lấy token
        $token = $matches[1];
        
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Token extracted: " . $token);
        }
        
        // Xác thực token
        $payload = $this->authService->validateAccessToken($token);
        
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Token validation result: " . ($payload ? json_encode($payload) : 'false'));
        }
        
        if (!$payload) {
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Kiểm tra xem người dùng có quyền truy cập tài nguyên không
     * Có thể mở rộng để kiểm tra quyền chi tiết hơn
     * 
     * @param string $resourceId ID của tài nguyên (nếu cần)
     * @param string $userId ID của người dùng đã xác thực
     * @return bool
     */
    public function authorize($resourceId, $userId) {
        // Mặc định cho phép truy cập
        // Có thể mở rộng để kiểm tra quyền chi tiết hơn
        return true;
    }
} 