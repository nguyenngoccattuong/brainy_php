<?php
namespace App\Models;

class UserNoteModel extends Model {
    protected $table = 'user_notes';
    
    /**
     * Lấy danh sách ghi chú của user
     */
    public function getByUserId($userId) {
        $sql = "SELECT un.*, w.word, w.phonetic, w.phonetic_text 
                FROM {$this->table} un 
                LEFT JOIN words w ON un.word_id = w.id 
                WHERE un.user_id = :user_id 
                ORDER BY un.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy ghi chú theo ID
     */
    public function getById($id) {
        $sql = "SELECT un.*, w.word, w.phonetic, w.phonetic_text 
                FROM {$this->table} un 
                LEFT JOIN words w ON un.word_id = w.id 
                WHERE un.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy ghi chú theo word_id
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
     * Tạo ghi chú mới
     */
    public function create($data) {
        return $this->insert($data);
    }
    
    /**
     * Cập nhật ghi chú
     */
    public function update($id, $data) {
        return $this->updateById($id, $data);
    }
    
    /**
     * Xóa ghi chú
     */
    public function delete($id) {
        return $this->deleteById($id);
    }
} 