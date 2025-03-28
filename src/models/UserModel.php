<?php
namespace App\Models;

/**
 * Class UserModel
 * Model xử lý dữ liệu bảng users
 */
class UserModel extends Model {
    protected $table = 'users';
    
    /**
     * Tìm người dùng theo username
     * 
     * @param string $username
     * @return array|bool
     */
    public function findByUsername($username) {
        return $this->findOneWhere(['username' => $username]);
    }
    
    /**
     * Tìm người dùng theo email
     * 
     * @param string $email
     * @return array|bool
     */
    public function findByEmail($email) {
        return $this->findOneWhere(['email' => $email]);
    }
    
    /**
     * Tạo người dùng mới
     * 
     * @param array $data Dữ liệu người dùng
     * @return string|bool UUID của người dùng mới hoặc false nếu thất bại
     */
    public function register($data) {
        // Hash mật khẩu trước khi lưu vào DB
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Cập nhật thông tin người dùng
     * 
     * @param string $id UUID của người dùng
     * @param array $data Dữ liệu cần cập nhật
     * @return bool
     */
    public function updateUser($id, $data) {
        // Hash mật khẩu nếu người dùng cập nhật mật khẩu
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Xác thực người dùng khi đăng nhập
     * 
     * @param string $username Username hoặc email
     * @param string $password Mật khẩu
     * @return array|bool Thông tin người dùng nếu xác thực thành công, ngược lại false
     */
    public function authenticate($username, $password) {
        // Tìm người dùng theo username hoặc email
        $user = $this->findByUsername($username);
        if (!$user) {
            $user = $this->findByEmail($username);
        }
        
        // Nếu không tìm thấy người dùng
        if (!$user) {
            return false;
        }
        
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            // Xóa password trước khi trả về thông tin người dùng
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
} 