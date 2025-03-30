<?php
namespace App\Models;

class PasswordResetModel extends Model {
    protected $table = 'password_resets';
    
    /**
     * Tạo token reset mật khẩu
     * 
     * @param string $userId UUID của người dùng
     * @param int $expiresInMinutes Thời gian hiệu lực của token (phút)
     * @return array|bool Thông tin token hoặc false nếu thất bại
     */
    public function createToken($userId, $expiresInMinutes = 60) {
        // Tạo token ngẫu nhiên
        $token = bin2hex(random_bytes(32));
        
        // Tính thời gian hết hạn
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInMinutes} minutes"));
        
        // Xóa token cũ nếu có
        $this->deleteByUserId($userId);
        
        // Lưu token mới
        $resetData = [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ];
        
        $id = $this->create($resetData);
        
        if ($id) {
            return $this->getById($id);
        }
        
        return false;
    }
    
    /**
     * Tìm token reset mật khẩu
     * 
     * @param string $token Token reset
     * @return array|bool Thông tin token hoặc false nếu không tìm thấy
     */
    public function findByToken($token) {
        return $this->findOneWhere(['token' => $token]);
    }
    
    /**
     * Xóa token reset mật khẩu theo user ID
     * 
     * @param string $userId UUID của người dùng
     * @return bool
     */
    public function deleteByUserId($userId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }
    
    /**
     * Xóa token reset mật khẩu theo token
     * 
     * @param string $token Token reset
     * @return bool
     */
    public function deleteByToken($token) {
        $sql = "DELETE FROM {$this->table} WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':token', $token);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra token có hợp lệ không
     * 
     * @param string $token Token reset
     * @return bool|array False nếu không hợp lệ, thông tin token nếu hợp lệ
     */
    public function validateToken($token) {
        $resetData = $this->findByToken($token);
        
        if (!$resetData) {
            return false;
        }
        
        // Kiểm tra xem token có hết hạn chưa
        if (strtotime($resetData['expires_at']) < time()) {
            $this->deleteByToken($token);
            return false;
        }
        
        return $resetData;
    }
} 