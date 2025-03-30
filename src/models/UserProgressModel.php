<?php
namespace App\Models;

class UserProgressModel extends Model {
    protected $table = 'user_progress';
    
    /**
     * Lấy danh sách tiến độ của user
     */
    public function getByUserId($userId) {
        $sql = "SELECT up.*, w.word, w.phonetic, w.phonetic_text 
                FROM {$this->table} up 
                LEFT JOIN words w ON up.word_id = w.id 
                WHERE up.user_id = :user_id 
                ORDER BY up.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy tiến độ của một từ cụ thể
     */
    public function getByWordId($userId, $wordId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND word_id = :word_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':word_id', $wordId);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Tạo hoặc cập nhật tiến độ
     */
    public function updateProgress($userId, $wordId, $data) {
        $existing = $this->getByWordId($userId, $wordId);
        
        if ($existing) {
            // Cập nhật tiến độ hiện có
            $sql = "UPDATE {$this->table} 
                    SET status = :status,
                        last_review = :last_review,
                        next_review = :next_review,
                        review_count = :review_count,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = :user_id AND word_id = :word_id";
        } else {
            // Tạo tiến độ mới
            $sql = "INSERT INTO {$this->table} 
                    (user_id, word_id, status, last_review, next_review, review_count) 
                    VALUES 
                    (:user_id, :word_id, :status, :last_review, :next_review, :review_count)";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':word_id', $wordId);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':last_review', $data['last_review']);
        $stmt->bindValue(':next_review', $data['next_review']);
        $stmt->bindValue(':review_count', $data['review_count']);
        
        return $stmt->execute();
    }
    
    /**
     * Xóa tiến độ
     */
    public function delete($userId, $wordId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND word_id = :word_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':word_id', $wordId);
        return $stmt->execute();
    }
    
    /**
     * Lấy danh sách từ cần review
     */
    public function getWordsToReview($userId) {
        $sql = "SELECT up.*, w.word, w.phonetic, w.phonetic_text 
                FROM {$this->table} up 
                LEFT JOIN words w ON up.word_id = w.id 
                WHERE up.user_id = :user_id 
                AND up.next_review <= CURRENT_TIMESTAMP 
                ORDER BY up.next_review ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
} 