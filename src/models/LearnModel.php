<?php
namespace App\Models;

class LearnModel extends Model {
    protected $table = 'learn';
    
    /**
     * Lấy danh sách tất cả trạng thái học của người dùng
     * 
     * @param string $userId UUID của người dùng
     * @return array
     */
    public function getByUserId($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        // Lấy tổng số records
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        // Lấy dữ liệu theo trang
        $sql = "SELECT l.*, w.word, w.pos, w.phonetic_text,
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url
                FROM {$this->table} l
                LEFT JOIN words w ON l.word_id = w.id
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                WHERE l.user_id = :user_id
                ORDER BY l.updated_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Lấy danh sách trạng thái học của người dùng theo status
     * 
     * @param string $userId UUID của người dùng
     * @param string $status Trạng thái học (skip, learned, learning)
     * @return array
     */
    public function getByUserIdAndStatus($userId, $status, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        // Lấy tổng số records
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE user_id = :user_id AND status = :status";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':status', $status);
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        // Lấy dữ liệu theo trang
        $sql = "SELECT l.*, w.word, w.pos, w.phonetic_text,
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url
                FROM {$this->table} l
                LEFT JOIN words w ON l.word_id = w.id
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                WHERE l.user_id = :user_id AND l.status = :status
                ORDER BY l.updated_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Lấy trạng thái học cụ thể của một từ
     * 
     * @param string $userId UUID của người dùng
     * @param string $wordId UUID của từ
     * @return array|false
     */
    public function getByUserIdAndWordId($userId, $wordId) {
        $sql = "SELECT l.*, w.word, w.pos, w.phonetic_text
                FROM {$this->table} l
                LEFT JOIN words w ON l.word_id = w.id
                WHERE l.user_id = :user_id AND l.word_id = :word_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':word_id', $wordId);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Cập nhật hoặc tạo mới trạng thái học
     * 
     * @param string $userId UUID của người dùng
     * @param string $wordId UUID của từ
     * @param string $status Trạng thái học (skip, learned, learning)
     * @return string|false UUID của bản ghi hoặc false nếu thất bại
     */
    public function updateOrCreate($userId, $wordId, $status) {
        // Kiểm tra xem đã tồn tại chưa
        $existing = $this->getByUserIdAndWordId($userId, $wordId);
        
        if ($existing) {
            // Cập nhật nếu đã tồn tại
            $this->update($existing['id'], ['status' => $status]);
            return $existing['id'];
        } else {
            // Tạo mới nếu chưa tồn tại
            return $this->create([
                'user_id' => $userId,
                'word_id' => $wordId,
                'status' => $status
            ]);
        }
    }
    
    /**
     * Xóa trạng thái học
     * 
     * @param string $userId UUID của người dùng
     * @param string $wordId UUID của từ
     * @return bool
     */
    public function deleteByUserIdAndWordId($userId, $wordId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND word_id = :word_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':word_id', $wordId);
        return $stmt->execute();
    }
} 