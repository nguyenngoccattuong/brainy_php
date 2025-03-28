<?php
namespace App\Middleware;

use App\Config\Database;
use App\Services\UserService;

/**
 * Class AuthMiddleware
 * Middleware xác thực người dùng
 */
class AuthMiddleware {
    private $userService;
    
    public function __construct() {
        $db = new Database();
        $this->userService = new UserService($db->connect());
    }
    
    /**
     * Xác thực người dùng thông qua JWT token
     * 
     * @return array|bool Thông tin người dùng đã xác thực hoặc false nếu xác thực thất bại
     */
    public function authenticate() {
        // Kiểm tra xem Authorization header có tồn tại không
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return false;
        }
        
        // Lấy token từ Authorization header (Bearer token)
        $authHeader = $headers['Authorization'];
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return false;
        }
        
        $token = $matches[1];
        
        // Xác thực token (trong ví dụ này sử dụng phương pháp đơn giản)
        return $this->validateToken($token);
    }
    
    /**
     * Xác thực JWT token
     * 
     * @param string $token JWT token
     * @return array|bool Thông tin người dùng hoặc false nếu token không hợp lệ
     */
    private function validateToken($token) {
        try {
            // Trong thực tế, bạn sẽ sử dụng thư viện JWT để xác thực token
            // Ví dụ này dùng phương pháp đơn giản để minh họa
            list($header, $payload, $signature) = explode('.', $token);
            
            // Giải mã payload
            $decodedPayload = json_decode(base64_decode($payload), true);
            
            if (!isset($decodedPayload['user_id']) || !isset($decodedPayload['exp'])) {
                return false;
            }
            
            // Kiểm tra token hết hạn
            if ($decodedPayload['exp'] < time()) {
                return false;
            }
            
            // Lấy thông tin người dùng từ database
            $userId = $decodedPayload['user_id'];
            $user = $this->userService->getUserById($userId);
            
            if (!$user) {
                return false;
            }
            
            // Trả về thông tin người dùng đã xác thực
            return $user;
        } catch (\Exception $e) {
            // Ghi log lỗi
            $logFile = __DIR__ . '/../../logs/auth_error.log';
            $message = date('Y-m-d H:i:s') . ' - Auth Error: ' . $e->getMessage() . "\n";
            file_put_contents($logFile, $message, FILE_APPEND);
            
            return false;
        }
    }
} 