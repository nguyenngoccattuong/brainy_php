<?php
namespace App\Services;

use App\Config\Database;
use App\Models\LearnModel;
use App\Models\WordModel;

class LearnService {
    private $learnModel;
    private $wordModel;
    
    public function __construct() {
        $db = new Database();
        $connection = $db->connect();
        $this->learnModel = new LearnModel($connection);
        $this->wordModel = new WordModel($connection);
    }
    
    /**
     * Lấy danh sách trạng thái học của người dùng
     * 
     * @param string $userId UUID của người dùng
     * @param int $page Số trang
     * @param int $limit Số lượng kết quả trên một trang
     * @return array
     */
    public function getLearnByUserId($userId, $page = 1, $limit = 10) {
        if (!$this->validateUuid($userId)) {
            throw new \Exception('User ID không hợp lệ');
        }
        
        return $this->learnModel->getByUserId($userId, $page, $limit);
    }
    
    /**
     * Lấy danh sách trạng thái học của người dùng theo status
     * 
     * @param string $userId UUID của người dùng
     * @param string $status Trạng thái học (skip, learned, learning)
     * @param int $page Số trang
     * @param int $limit Số lượng kết quả trên một trang
     * @return array
     */
    public function getLearnByStatus($userId, $status, $page = 1, $limit = 10) {
        if (!$this->validateUuid($userId)) {
            throw new \Exception('User ID không hợp lệ');
        }
        
        if (!in_array($status, ['skip', 'learned', 'learning'])) {
            throw new \Exception('Status không hợp lệ');
        }
        
        return $this->learnModel->getByUserIdAndStatus($userId, $status, $page, $limit);
    }
    
    /**
     * Lấy trạng thái học cụ thể của một từ
     * 
     * @param string $userId UUID của người dùng
     * @param string $wordId UUID của từ
     * @return array
     */
    public function getLearnStatus($userId, $wordId) {
        if (!$this->validateUuid($userId) || !$this->validateUuid($wordId)) {
            throw new \Exception('User ID hoặc Word ID không hợp lệ');
        }
        
        $learn = $this->learnModel->getByUserIdAndWordId($userId, $wordId);
        
        if (!$learn) {
            throw new \Exception('Không tìm thấy thông tin học từ này');
        }
        
        return $learn;
    }
    
    /**
     * Cập nhật trạng thái học
     * 
     * @param string $userId UUID của người dùng
     * @param string $wordId UUID của từ
     * @param string $status Trạng thái học (skip, learned, learning)
     * @return array
     */
    public function updateLearnStatus($userId, $wordId, $status) {
        if (!$this->validateUuid($userId) || !$this->validateUuid($wordId)) {
            throw new \Exception('User ID hoặc Word ID không hợp lệ');
        }
        
        if (!in_array($status, ['skip', 'learned', 'learning'])) {
            throw new \Exception('Status không hợp lệ');
        }
        
        // Kiểm tra từ vựng tồn tại
        $word = $this->wordModel->getById($wordId);
        if (!$word) {
            throw new \Exception('Từ vựng không tồn tại');
        }
        
        // Cập nhật hoặc tạo mới
        $learnId = $this->learnModel->updateOrCreate($userId, $wordId, $status);
        
        if (!$learnId) {
            throw new \Exception('Không thể cập nhật trạng thái học');
        }
        
        // Lấy thông tin sau khi cập nhật
        return $this->learnModel->getByUserIdAndWordId($userId, $wordId);
    }
    
    /**
     * Xóa trạng thái học
     * 
     * @param string $userId UUID của người dùng
     * @param string $wordId UUID của từ
     * @return bool
     */
    public function deleteLearnStatus($userId, $wordId) {
        if (!$this->validateUuid($userId) || !$this->validateUuid($wordId)) {
            throw new \Exception('User ID hoặc Word ID không hợp lệ');
        }
        
        // Kiểm tra tồn tại
        $learn = $this->learnModel->getByUserIdAndWordId($userId, $wordId);
        if (!$learn) {
            throw new \Exception('Không tìm thấy thông tin học từ này');
        }
        
        // Xóa
        $deleted = $this->learnModel->deleteByUserIdAndWordId($userId, $wordId);
        
        if (!$deleted) {
            throw new \Exception('Không thể xóa trạng thái học');
        }
        
        return true;
    }
    
    /**
     * Kiểm tra UUID hợp lệ
     * 
     * @param string|null $uuid UUID cần kiểm tra
     * @return bool
     */
    private function validateUuid($uuid) {
        if ($uuid === null || !is_string($uuid)) {
            return false;
        }
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid);
    }
} 