<?php
namespace App\Models;

/**
 * Class WordModel
 * Model xử lý dữ liệu bảng words
 */
class WordModel extends Model {
    protected $table = 'words';
    
    /**
     * Tìm từ vựng theo text
     * 
     * @param string $word Từ vựng cần tìm
     * @return array|bool
     */
    public function findByWord($word) {
        return $this->findOneWhere(['word' => $word]);
    }
    
    /**
     * Lấy các từ vựng theo lesson
     * 
     * @param string $lessonId UUID của bài học
     * @param array $columns Các cột cần lấy
     * @return array
     */
    public function getByLessonId($lessonId, $columns = ['*']) {
        $columnsStr = $columns[0] === '*' ? '*' : implode(', ', $columns);
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE lesson_id = :lesson_id ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':lesson_id', $lessonId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy toàn bộ thông tin của từ vựng bao gồm cả nghĩa và ví dụ
     * 
     * @param string $wordId UUID của từ vựng
     * @return array
     */
    public function getWordWithDetails($wordId) {
        $sql = "SELECT w.*, 
                cf_audio.file_url as audio_url, 
                cf_image.file_url as image_url
                FROM {$this->table} w
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id
                WHERE w.id = :id";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $wordId);
        $stmt->execute();
        
        $word = $stmt->fetch();
        
        if ($word) {
            // Lấy danh sách senses (nghĩa)
            $sql = "SELECT * FROM senses WHERE word_id = :word_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':word_id', $wordId);
            $stmt->execute();
            $senses = $stmt->fetchAll();
            
            if ($senses) {
                foreach ($senses as $key => $sense) {
                    // Lấy danh sách examples (ví dụ) cho mỗi sense
                    $sql = "SELECT * FROM examples WHERE sense_id = :sense_id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':sense_id', $sense['id']);
                    $stmt->execute();
                    $examples = $stmt->fetchAll();
                    
                    $senses[$key]['examples'] = $examples;
                }
            }
            
            $word['senses'] = $senses;
        }
        
        return $word;
    }
    
    /**
     * Lưu nghĩa của từ vựng
     * 
     * @param string $wordId UUID của từ vựng
     * @param string $definition Nghĩa của từ
     * @return string|bool UUID của sense hoặc false nếu thất bại
     */
    public function saveSense($wordId, $definition) {
        $sense = [
            'word_id' => $wordId,
            'definition' => $definition
        ];
        
        $columns = implode(', ', array_keys($sense));
        $placeholders = ':' . implode(', :', array_keys($sense));
        
        // Tạo UUID mới
        $senseId = bin2hex(random_bytes(16));
        $senseId = sprintf(
            '%08s-%04s-%04s-%04s-%012s',
            substr($senseId, 0, 8),
            substr($senseId, 8, 4),
            substr($senseId, 12, 4),
            substr($senseId, 16, 4),
            substr($senseId, 20, 12)
        );
        $sense['id'] = $senseId;
        
        $sql = "INSERT INTO senses (id, {$columns}) VALUES (:id, {$placeholders})";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($sense as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        if ($stmt->execute()) {
            return $senseId;
        }
        
        return false;
    }
    
    /**
     * Lưu ví dụ cho nghĩa của từ vựng
     * 
     * @param string $senseId UUID của sense
     * @param string $example Câu ví dụ
     * @param string $cf Context form (tùy chọn)
     * @return string|bool UUID của example hoặc false nếu thất bại
     */
    public function saveExample($senseId, $example, $cf = null) {
        $exampleData = [
            'sense_id' => $senseId,
            'x' => $example
        ];
        
        if ($cf) {
            $exampleData['cf'] = $cf;
        }
        
        $columns = implode(', ', array_keys($exampleData));
        $placeholders = ':' . implode(', :', array_keys($exampleData));
        
        // Tạo UUID mới
        $exampleId = bin2hex(random_bytes(16));
        $exampleId = sprintf(
            '%08s-%04s-%04s-%04s-%012s',
            substr($exampleId, 0, 8),
            substr($exampleId, 8, 4),
            substr($exampleId, 12, 4),
            substr($exampleId, 16, 4),
            substr($exampleId, 20, 12)
        );
        $exampleData['id'] = $exampleId;
        
        $sql = "INSERT INTO examples (id, {$columns}) VALUES (:id, {$placeholders})";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($exampleData as $column => $value) {
            $stmt->bindValue(":{$column}", $value);
        }
        
        if ($stmt->execute()) {
            return $exampleId;
        }
        
        return false;
    }
    
    /**
     * Xóa từ vựng cùng các nghĩa và ví dụ liên quan
     * 
     * @param string $wordId UUID của từ
     * @return bool
     */
    public function deleteWordWithDetails($wordId) {
        try {
            $this->conn->beginTransaction();
            
            // Lấy danh sách senses
            $sql = "SELECT id FROM senses WHERE word_id = :word_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':word_id', $wordId);
            $stmt->execute();
            $senses = $stmt->fetchAll();
            
            // Xóa examples của từng sense
            foreach ($senses as $sense) {
                $sql = "DELETE FROM examples WHERE sense_id = :sense_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':sense_id', $sense['id']);
                $stmt->execute();
            }
            
            // Xóa senses
            $sql = "DELETE FROM senses WHERE word_id = :word_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':word_id', $wordId);
            $stmt->execute();
            
            // Xóa user_progress và user_notes
            $sql = "DELETE FROM user_progress WHERE word_id = :word_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':word_id', $wordId);
            $stmt->execute();
            
            $sql = "DELETE FROM user_notes WHERE word_id = :word_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':word_id', $wordId);
            $stmt->execute();
            
            // Xóa từ
            $result = $this->delete($wordId);
            
            $this->conn->commit();
            return $result;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
} 