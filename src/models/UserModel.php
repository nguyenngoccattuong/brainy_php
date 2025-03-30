<?php
namespace App\Models;

class UserModel extends Model {
    protected $table = 'users';
    
    /**
     * Đăng ký người dùng mới
     * 
     * @param array $data Dữ liệu người dùng
     * @return string|bool UUID của người dùng mới hoặc false nếu thất bại
     */
    public function register($data) {
        try {
            // Kiểm tra username đã tồn tại
            if ($this->findByUsername($data['username'])) {
                error_log("Register Error: Username {$data['username']} already exists");
                return false;
            }
            
            // Kiểm tra email đã tồn tại
            if ($this->findByEmail($data['email'])) {
                error_log("Register Error: Email {$data['email']} already exists");
                return false;
            }
            
            // Hash mật khẩu
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Thêm status nếu chưa có
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }
            
            // Tạo người dùng mới
            return $this->create($data);
        } catch (\Exception $e) {
            error_log("Register Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Xác thực người dùng
     * 
     * @param string $username Username hoặc email
     * @param string $password Mật khẩu
     * @return array|bool Thông tin người dùng hoặc false nếu thất bại
     */
    public function authenticate($username, $password) {
        try {
            // Tìm người dùng theo username hoặc email
            $sql = "SELECT * FROM {$this->table} WHERE username = :username OR email = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $username);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                error_log("Authentication Error: User not found - $username");
                return false;
            }
            
            // Kiểm tra mật khẩu
            if (!password_verify($password, $user['password'])) {
                error_log("Authentication Error: Invalid password for user - $username");
                return false;
            }
            
            unset($user['password']);
            return $user;
        } catch (\Exception $e) {
            error_log("Authentication Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tìm người dùng theo username
     * 
     * @param string $username Username cần tìm
     * @return array|bool Thông tin người dùng hoặc false nếu không tìm thấy
     */
    public function findByUsername($username) {
        try {
            return $this->findOneWhere(['username' => $username]);
        } catch (\Exception $e) {
            error_log("FindByUsername Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tìm người dùng theo email
     * 
     * @param string $email Email cần tìm
     * @return array|bool Thông tin người dùng hoặc false nếu không tìm thấy
     */
    public function findByEmail($email) {
        try {
            return $this->findOneWhere(['email' => $email]);
        } catch (\Exception $e) {
            error_log("FindByEmail Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cập nhật thông tin người dùng
     * 
     * @param string $id UUID của người dùng
     * @param array $data Dữ liệu cần cập nhật
     * @return bool
     */
    public function updateUser($id, $data) {
        try {
            // Hash mật khẩu nếu có
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            return $this->update($id, $data);
        } catch (\Exception $e) {
            error_log("UpdateUser Error: " . $e->getMessage());
            return false;
        }
    }
} 