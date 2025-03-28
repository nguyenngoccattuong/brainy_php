<?php
namespace App\Services;

use App\Models\UserModel;

/**
 * Class UserService
 * Service xử lý logic nghiệp vụ liên quan đến người dùng
 */
class UserService {
    private $userModel;
    
    /**
     * Constructor
     * 
     * @param \PDO $conn Kết nối PDO đến database
     */
    public function __construct($conn) {
        $this->userModel = new UserModel($conn);
    }
    
    /**
     * Lấy tất cả người dùng
     * 
     * @return array
     */
    public function getAllUsers() {
        return $this->userModel->getAll();
    }
    
    /**
     * Lấy thông tin người dùng theo ID
     * 
     * @param string $id UUID của người dùng
     * @return array|bool
     */
    public function getUserById($id) {
        $user = $this->userModel->getById($id);
        
        if ($user) {
            // Xóa mật khẩu khỏi dữ liệu trả về
            unset($user['password']);
        }
        
        return $user;
    }
    
    /**
     * Tạo người dùng mới
     * 
     * @param array $data Dữ liệu người dùng
     * @return array|bool Thông tin người dùng mới hoặc false nếu thất bại
     */
    public function createUser($data) {
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
        
        if ($userId) {
            return $this->getUserById($userId);
        }
        
        return false;
    }
    
    /**
     * Cập nhật thông tin người dùng
     * 
     * @param string $id UUID của người dùng
     * @param array $data Dữ liệu cần cập nhật
     * @return array|bool Thông tin người dùng sau khi cập nhật hoặc false nếu thất bại
     */
    public function updateUser($id, $data) {
        // Kiểm tra xem người dùng có tồn tại không
        $user = $this->userModel->getById($id);
        if (!$user) {
            throw new \Exception('Không tìm thấy người dùng');
        }
        
        // Kiểm tra xem username mới đã tồn tại chưa (nếu có thay đổi)
        if (isset($data['username']) && $data['username'] !== $user['username']) {
            $existingUser = $this->userModel->findByUsername($data['username']);
            if ($existingUser) {
                throw new \Exception('Username đã tồn tại');
            }
        }
        
        // Kiểm tra xem email mới đã tồn tại chưa (nếu có thay đổi)
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                throw new \Exception('Email đã tồn tại');
            }
        }
        
        // Cập nhật thông tin người dùng
        $updated = $this->userModel->updateUser($id, $data);
        
        if ($updated) {
            return $this->getUserById($id);
        }
        
        return false;
    }
    
    /**
     * Xóa người dùng
     * 
     * @param string $id UUID của người dùng
     * @return bool
     */
    public function deleteUser($id) {
        // Kiểm tra xem người dùng có tồn tại không
        $user = $this->userModel->getById($id);
        if (!$user) {
            throw new \Exception('Không tìm thấy người dùng');
        }
        
        return $this->userModel->delete($id);
    }
    
    /**
     * Xác thực người dùng (đăng nhập)
     * 
     * @param string $username Username hoặc email
     * @param string $password Mật khẩu
     * @return array|bool Thông tin người dùng nếu xác thực thành công, ngược lại false
     */
    public function authenticate($username, $password) {
        return $this->userModel->authenticate($username, $password);
    }
    
    /**
     * Tạo JWT token cho người dùng
     * 
     * @param array $user Thông tin người dùng
     * @return string JWT token
     */
    public function generateToken($user) {
        // Tạo payload cho JWT
        $payload = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'iat' => time(), // Issued at
            'exp' => time() + (60 * 60 * 24) // Hết hạn sau 24h
        ];
        
        // Mã hóa payload thành base64
        $base64Payload = base64_encode(json_encode($payload));
        
        // Trong thực tế, bạn sẽ sử dụng thư viện JWT để tạo token
        // Đây chỉ là ví dụ đơn giản
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $signature = hash_hmac('sha256', "$header.$base64Payload", $_ENV['JWT_SECRET'] ?? 'brainy_secret_key');
        $base64Signature = base64_encode($signature);
        
        // Tạo JWT token
        return "$header.$base64Payload.$base64Signature";
    }
} 