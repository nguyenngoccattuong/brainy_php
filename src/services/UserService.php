<?php
namespace App\Services;

use App\Config\Database;
use App\Models\UserModel;
use App\Models\UserProgressModel;
use App\Models\UserNoteModel;
use App\Controllers\CloudinaryController;

class UserService {
    private $userModel;
    private $userProgressModel;
    private $userNoteModel;
    private $cloudinaryController;
    
    public function __construct() {
        $db = new Database();
        $connection = $db->connect();
        $this->userModel = new UserModel($connection);
        $this->userProgressModel = new UserProgressModel($connection);
        $this->userNoteModel = new UserNoteModel($connection);
        $this->cloudinaryController = new CloudinaryController();
    }
    
    /**
     * Lấy tất cả users
     */
    public function getAllUsers() {
        $users = $this->userModel->getAll();
        // Loại bỏ password trước khi trả về
        foreach ($users as &$user) {
            unset($user['password']);
        }
        return $users;
    }
    
    /**
     * Lấy user theo ID
     */
    public function getUserById($userId) {
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new \Exception('Không tìm thấy user');
        }
        unset($user['password']);
        return $user;
    }
    
    /**
     * Tạo user mới
     */
    public function createUser($data) {
        // Kiểm tra username đã tồn tại
        if ($this->userModel->findByUsername($data['username'])) {
            throw new \Exception('Username đã tồn tại');
        }
        
        // Kiểm tra email đã tồn tại
        if ($this->userModel->findByEmail($data['email'])) {
            throw new \Exception('Email đã tồn tại');
        }

        // Xử lý upload avatar nếu có
        if (isset($data['avatar']) && !empty($data['avatar'])) {
            $uploadResult = $this->cloudinaryController->upload([
                'file' => $data['avatar'],
                'owner_type' => 'User',
                'owner_id' => 'temp'
            ]);

            if (isset($uploadResult['error'])) {
                throw new \Exception('Không thể upload avatar: ' . $uploadResult['error']);
            }

            $data['avatar_url'] = $uploadResult['file']['url'];
        }

        // Tạo user
        $userId = $this->userModel->register($data);
        if (!$userId) {
            throw new \Exception('Không thể tạo user');
        }

        // Cập nhật owner_id cho avatar nếu có
        if (isset($uploadResult)) {
            $this->cloudinaryController->upload([
                'file_id' => $uploadResult['file']['id'],
                'owner_id' => $userId
            ]);
        }

        $user = $this->userModel->getById($userId);
        unset($user['password']);
        return $user;
    }
    
    /**
     * Cập nhật user
     */
    public function updateUser($userId, $data) {
        // Kiểm tra user tồn tại
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new \Exception('Không tìm thấy user');
        }

        // Kiểm tra username mới không trùng với user khác
        if (isset($data['username']) && $data['username'] !== $user['username']) {
            if ($this->userModel->findByUsername($data['username'])) {
                throw new \Exception('Username đã tồn tại');
            }
        }

        // Kiểm tra email mới không trùng với user khác
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if ($this->userModel->findByEmail($data['email'])) {
                throw new \Exception('Email đã tồn tại');
            }
        }

        // Xử lý upload avatar mới nếu có
        if (isset($data['avatar']) && !empty($data['avatar'])) {
            $uploadResult = $this->cloudinaryController->upload([
                'file' => $data['avatar'],
                'owner_type' => 'User',
                'owner_id' => $userId
            ]);

            if (isset($uploadResult['error'])) {
                throw new \Exception('Không thể upload avatar: ' . $uploadResult['error']);
            }

            $data['avatar_url'] = $uploadResult['file']['url'];
        }

        // Cập nhật thông tin
        $updated = $this->userModel->updateUser($userId, $data);
        if (!$updated) {
            throw new \Exception('Không thể cập nhật thông tin');
        }

        $user = $this->userModel->getById($userId);
        unset($user['password']);
        return $user;
    }
    
    /**
     * Xóa user
     */
    public function deleteUser($userId) {
        // Kiểm tra user tồn tại
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new \Exception('Không tìm thấy user');
        }

        // Xóa avatar trên Cloudinary nếu có
        if ($user['avatar_url']) {
            // Lấy public_id từ avatar_url
            $publicId = $this->extractPublicIdFromUrl($user['avatar_url']);
            if ($publicId) {
                $this->cloudinaryController->delete($publicId);
            }
        }

        // Xóa user
        $deleted = $this->userModel->delete($userId);
        if (!$deleted) {
            throw new \Exception('Không thể xóa user');
        }

        return true;
    }

    /**
     * Lấy tiến độ học của user
     */
    public function getUserProgress($userId) {
        return $this->userProgressModel->getByUserId($userId);
    }

    /**
     * Cập nhật tiến độ học
     */
    public function updateUserProgress($userId, $wordId, $data) {
        return $this->userProgressModel->updateProgress($userId, $wordId, $data);
    }
    
    /**
     * Xóa tiến độ học
     */
    public function deleteUserProgress($userId, $wordId) {
        return $this->userProgressModel->deleteProgress($userId, $wordId);
    }

    /**
     * Lấy ghi chú của user
     */
    public function getUserNotes($userId) {
        return $this->userNoteModel->getByUserId($userId);
    }

    /**
     * Tạo/cập nhật ghi chú
     */
    public function saveUserNote($userId, $wordId, $note) {
        $existingNote = $this->userNoteModel->getByWordId($userId, $wordId);
        if ($existingNote) {
            return $this->userNoteModel->update($existingNote['id'], ['note' => $note]);
        } else {
            return $this->userNoteModel->create([
                'user_id' => $userId,
                'word_id' => $wordId,
                'note' => $note
            ]);
        }
    }

    /**
     * Xóa ghi chú
     */
    public function deleteUserNote($userId, $wordId) {
        $note = $this->userNoteModel->getByWordId($userId, $wordId);
        if ($note) {
            return $this->userNoteModel->delete($note['id']);
        }
        return true;
    }

    /**
     * Extract public_id from Cloudinary URL
     */
    private function extractPublicIdFromUrl($url) {
        // Example URL: https://res.cloudinary.com/demo/image/upload/v1234567890/folder/image.jpg
        if (preg_match('/\/v\d+\/(.+)\.\w+$/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
} 