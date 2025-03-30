<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\PasswordResetModel;
use App\Models\RefreshTokenModel;

/**
 * Class AuthService
 * Service xử lý logic nghiệp vụ liên quan đến xác thực và quản lý tài khoản
 */
class AuthService {
    private $userModel;
    private $passwordResetModel;
    private $refreshTokenModel;
    
    /**
     * Constructor
     * 
     * @param \PDO $conn Kết nối PDO đến database
     */
    public function __construct($conn) {
        $this->userModel = new UserModel($conn);
        $this->passwordResetModel = new PasswordResetModel($conn);
        $this->refreshTokenModel = new RefreshTokenModel($conn);
    }
    
    /**
     * Đăng ký người dùng mới
     * 
     * @param array $data Dữ liệu đăng ký
     * @return array Thông tin người dùng và tokens
     */
    public function register($data) {
        // Kiểm tra xem username đã tồn tại chưa
        if ($this->userModel->findByUsername($data['username'])) {
            throw new \Exception('Username đã tồn tại');
        }
        
        // Kiểm tra xem email đã tồn tại chưa
        if ($this->userModel->findByEmail($data['email'])) {
            throw new \Exception('Email đã tồn tại');
        }
        
        // Tạo người dùng mới
        $userId = $this->userModel->register($data);
        
        if (!$userId) {
            throw new \Exception('Không thể tạo tài khoản');
        }
        
        // Lấy thông tin người dùng
        $user = $this->userModel->getById($userId);
        unset($user['password']);
        
        // Tạo JWT token
        $accessToken = $this->generateAccessToken($user);
        
        // Tạo refresh token
        $refreshTokenData = $this->refreshTokenModel->createToken($userId);
        
        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenData['token'],
            'expires_in' => 3600 // Access token expires in 1 hour
        ];
    }
    
    /**
     * Đăng nhập
     * 
     * @param string $username Username hoặc email
     * @param string $password Mật khẩu
     * @return array Thông tin người dùng và tokens
     */
    public function login($username, $password) {
        // Xác thực người dùng
        $user = $this->userModel->authenticate($username, $password);
        
        if (!$user) {
            throw new \Exception('Username/email hoặc mật khẩu không chính xác');
        }
        
        // Kiểm tra trạng thái tài khoản
        if ($user['status'] !== 'active') {
            throw new \Exception('Tài khoản đã bị khóa');
        }
        
        // Tạo JWT token
        $accessToken = $this->generateAccessToken($user);
        
        // Tạo refresh token
        $refreshTokenData = $this->refreshTokenModel->createToken($user['id']);
        
        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenData['token'],
            'expires_in' => 3600 // Access token expires in 1 hour
        ];
    }
    
    /**
     * Đăng xuất
     * 
     * @param string $refreshToken Refresh token
     * @return bool
     */
    public function logout($refreshToken) {
        // Xóa refresh token
        return $this->refreshTokenModel->deleteByToken($refreshToken);
    }
    
    /**
     * Đăng xuất khỏi tất cả thiết bị
     * 
     * @param string $userId UUID của người dùng
     * @return bool
     */
    public function logoutAll($userId) {
        // Xóa tất cả refresh token của user
        return $this->refreshTokenModel->deleteAllByUserId($userId);
    }
    
    /**
     * Refresh token
     * 
     * @param string $refreshToken Refresh token
     * @return array Tokens mới
     */
    public function refreshToken($refreshToken) {
        // Kiểm tra refresh token
        $tokenData = $this->refreshTokenModel->validateToken($refreshToken);
        
        if (!$tokenData) {
            throw new \Exception('Refresh token không hợp lệ hoặc đã hết hạn');
        }
        
        // Lấy thông tin người dùng
        $user = $this->userModel->getById($tokenData['user_id']);
        unset($user['password']);
        
        // Tạo JWT token mới
        $accessToken = $this->generateAccessToken($user);
        
        // Tạo refresh token mới (rotate token)
        $this->refreshTokenModel->deleteByToken($refreshToken);
        $newRefreshTokenData = $this->refreshTokenModel->createToken($user['id']);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshTokenData['token'],
            'expires_in' => 3600 // Access token expires in 1 hour
        ];
    }
    
    /**
     * Yêu cầu reset mật khẩu
     * 
     * @param string $email Email của người dùng
     * @return array Thông tin token reset
     */
    public function forgotPassword($email) {
        // Tìm người dùng theo email
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            throw new \Exception('Email không tồn tại trong hệ thống');
        }
        
        // Tạo token reset password
        $resetData = $this->passwordResetModel->createToken($user['id']);
        
        if (!$resetData) {
            throw new \Exception('Không thể tạo token reset mật khẩu');
        }
        
        return [
            'message' => 'Đã gửi email reset mật khẩu',
            'token' => $resetData['token'],
            'expires_at' => $resetData['expires_at']
        ];
    }
    
    /**
     * Xác thực token reset mật khẩu
     * 
     * @param string $token Token reset
     * @return array Thông tin token
     */
    public function validateResetToken($token) {
        $resetData = $this->passwordResetModel->validateToken($token);
        
        if (!$resetData) {
            throw new \Exception('Token không hợp lệ hoặc đã hết hạn');
        }
        
        return [
            'valid' => true,
            'user_id' => $resetData['user_id'],
            'expires_at' => $resetData['expires_at']
        ];
    }
    
    /**
     * Reset mật khẩu
     * 
     * @param string $token Token reset
     * @param string $password Mật khẩu mới
     * @return array Thông tin người dùng
     */
    public function resetPassword($token, $password) {
        // Kiểm tra token
        $resetData = $this->passwordResetModel->validateToken($token);
        
        if (!$resetData) {
            throw new \Exception('Token không hợp lệ hoặc đã hết hạn');
        }
        
        // Cập nhật mật khẩu
        $updated = $this->userModel->updateUser($resetData['user_id'], [
            'password' => $password
        ]);
        
        if (!$updated) {
            throw new \Exception('Không thể cập nhật mật khẩu');
        }
        
        // Xóa token sau khi đã sử dụng
        $this->passwordResetModel->deleteByToken($token);
        
        // Đăng xuất khỏi tất cả thiết bị
        $this->refreshTokenModel->deleteAllByUserId($resetData['user_id']);
        
        // Lấy thông tin người dùng
        $user = $this->userModel->getById($resetData['user_id']);
        unset($user['password']);
        
        return [
            'message' => 'Đã cập nhật mật khẩu thành công',
            'user' => $user
        ];
    }
    
    /**
     * Thay đổi mật khẩu
     * 
     * @param string $userId UUID của người dùng
     * @param string $currentPassword Mật khẩu hiện tại
     * @param string $newPassword Mật khẩu mới
     * @return array Thông tin người dùng
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Lấy thông tin người dùng
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            throw new \Exception('Không tìm thấy người dùng');
        }
        
        // Kiểm tra mật khẩu hiện tại
        if (!password_verify($currentPassword, $user['password'])) {
            throw new \Exception('Mật khẩu hiện tại không chính xác');
        }
        
        // Cập nhật mật khẩu
        $updated = $this->userModel->updateUser($userId, [
            'password' => $newPassword
        ]);
        
        if (!$updated) {
            throw new \Exception('Không thể cập nhật mật khẩu');
        }
        
        // Lấy thông tin người dùng
        $user = $this->userModel->getById($userId);
        unset($user['password']);
        
        return [
            'message' => 'Đã cập nhật mật khẩu thành công',
            'user' => $user
        ];
    }
    
    /**
     * Tạo JWT access token
     * 
     * @param array $user Thông tin người dùng
     * @return string JWT token
     */
    private function generateAccessToken($user) {
        // Tạo payload cho JWT
        $payload = [
            'sub' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'iat' => time(), // Issued at
            'exp' => time() + 3600 // Expires in 1 hour
        ];
        
        // Mã hóa payload thành base64url
        $base64Header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));
        
        // Tạo signature
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $_ENV['JWT_SECRET'] ?? 'brainy_secret_key', true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        // Tạo JWT token
        return "$base64Header.$base64Payload.$base64Signature";
    }
    
    /**
     * Xác thực JWT token
     * 
     * @param string $token JWT token
     * @return array|bool Thông tin payload hoặc false nếu không hợp lệ
     */
    public function validateAccessToken($token) {
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Validating token: " . $token);
        }
        
        // Tách token thành các phần
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Invalid token format - expected 3 parts, got " . count($parts));
            }
            return false;
        }
        
        // Lấy header và payload
        $header = json_decode($this->base64UrlDecode($parts[0]), true);
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Decoded header: " . json_encode($header));
            error_log("Decoded payload: " . json_encode($payload));
        }
        
        if (!$header || !$payload) {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Failed to decode header or payload");
            }
            return false;
        }
        
        // Kiểm tra thuật toán
        if ($header['alg'] !== 'HS256') {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Invalid algorithm - expected HS256, got " . $header['alg']);
            }
            return false;
        }
        
        // Kiểm tra chữ ký
        $signature = hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $_ENV['JWT_SECRET'] ?? 'brainy_secret_key', true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Expected signature: " . $base64Signature);
            error_log("Received signature: " . $parts[2]);
        }
        
        if ($base64Signature !== $parts[2]) {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Invalid signature");
            }
            return false;
        }
        
        // Kiểm tra token có hết hạn chưa
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Token expired at " . date('Y-m-d H:i:s', $payload['exp']));
            }
            return false;
        }
        
        return $payload;
    }

    /**
     * Mã hóa chuỗi thành base64url
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Giải mã chuỗi base64url
     * 
     * @param string $data
     * @return string
     */
    private function base64UrlDecode($data) {
        $padding = strlen($data) % 4;
        if ($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
} 