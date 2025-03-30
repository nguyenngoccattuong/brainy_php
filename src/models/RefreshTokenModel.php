<?php
namespace App\Models;

class RefreshTokenModel extends Model {
    protected $table = 'refresh_tokens';
    
    /**
     * Tạo refresh token mới
     * 
     * @param string $userId UUID của người dùng
     * @param int $expiresInDays Thời gian hiệu lực của token (ngày)
     * @return array|bool Thông tin token hoặc false nếu thất bại
     */
    public function createToken($userId, $expiresInDays = 30) {
        // Tạo token ngẫu nhiên
        $token = bin2hex(random_bytes(64));
        
        // Tính thời gian hết hạn
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiresInDays} days"));
        
        // Lưu token mới
        $tokenData = [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ];
        
        $id = $this->create($tokenData);
        
        if ($id) {
            return $this->getById($id);
        }
        
        return false;
    }
    
    /**
     * Tìm refresh token
     * 
     * @param string $token Refresh token
     * @return array|bool Thông tin token hoặc false nếu không tìm thấy
     */
    public function findByToken($token) {
        return $this->findOneWhere(['token' => $token]);
    }
    
    /**
     * Xóa refresh token theo token
     * 
     * @param string $token Refresh token
     * @return bool
     */
    public function deleteByToken($token) {
        $sql = "DELETE FROM {$this->table} WHERE token = :token";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':token', $token);
        return $stmt->execute();
    }
    
    /**
     * Xóa tất cả refresh token của user
     * 
     * @param string $userId UUID của người dùng
     * @return bool
     */
    public function deleteAllByUserId($userId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        return $stmt->execute();
    }
    
    /**
     * Xóa các token hết hạn
     * 
     * @return bool
     */
    public function deleteExpiredTokens() {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra token có hợp lệ không
     * 
     * @param string $token Refresh token
     * @return bool|array False nếu không hợp lệ, thông tin token nếu hợp lệ
     */
    public function validateToken($token) {
        $tokenData = $this->findByToken($token);
        
        if (!$tokenData) {
            return false;
        }
        
        // Kiểm tra xem token có hết hạn chưa
        if (strtotime($tokenData['expires_at']) < time()) {
            $this->deleteByToken($token);
            return false;
        }
        
        return $tokenData;
    }
} 