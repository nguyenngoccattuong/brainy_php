<?php
namespace App\Models;

class WordModel extends Model {
    protected $table = 'words';
    
    /**
     * Lấy danh sách tất cả words với phân trang
     */
    public function getAllPaginated($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        // Lấy tổng số records
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $total = $stmt->fetch()['total'];
        
        // Lấy dữ liệu theo trang
        $sql = "SELECT w.*, 
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url,
                l.title as lesson_title
                FROM {$this->table} w 
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                LEFT JOIN lessons l ON w.lesson_id = l.id 
                ORDER BY w.created_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Lấy danh sách tất cả words
     */
    public function getAll() {
        $sql = "SELECT w.*, 
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url,
                l.title as lesson_title
                FROM {$this->table} w 
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                LEFT JOIN lessons l ON w.lesson_id = l.id 
                ORDER BY w.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy word theo ID
     */
    public function getById($id) {
        $sql = "SELECT w.*, 
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url,
                l.title as lesson_title
                FROM {$this->table} w 
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                LEFT JOIN lessons l ON w.lesson_id = l.id 
                WHERE w.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy words theo lesson_id
     */
    public function getByLessonId($lessonId) {
        $sql = "SELECT w.*, 
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url
                FROM {$this->table} w 
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                WHERE w.lesson_id = :lesson_id 
                ORDER BY w.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':lesson_id', $lessonId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tìm kiếm words
     */
    public function search($keyword) {
        // Chuẩn bị từ khóa tìm kiếm với LIKE
        $likeKeyword = "%{$keyword}%";
        
        $sql = "SELECT w.*, 
                cf_audio.file_url as audio_url,
                cf_image.file_url as image_url,
                l.title as lesson_title
                FROM {$this->table} w 
                LEFT JOIN cloudinary_files cf_audio ON w.audio_id = cf_audio.id 
                LEFT JOIN cloudinary_files cf_image ON w.image_id = cf_image.id 
                LEFT JOIN lessons l ON w.lesson_id = l.id 
                LEFT JOIN senses s ON w.id = s.word_id 
                LEFT JOIN examples e ON s.id = e.sense_id 
                WHERE w.word LIKE :word_like 
                OR w.pos LIKE :pos_like 
                OR w.phonetic_text LIKE :phonetic_like 
                OR s.definition LIKE :def_like 
                OR e.x LIKE :example_like 
                GROUP BY w.id
                ORDER BY 
                    CASE 
                        WHEN w.word = :exact_keyword THEN 1
                        WHEN w.word LIKE :start_keyword THEN 2
                        ELSE 3
                    END,
                    w.word ASC";
                    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':word_like', $likeKeyword);
        $stmt->bindValue(':pos_like', $likeKeyword);
        $stmt->bindValue(':phonetic_like', $likeKeyword);
        $stmt->bindValue(':def_like', $likeKeyword);
        $stmt->bindValue(':example_like', $likeKeyword);
        $stmt->bindValue(':exact_keyword', $keyword);
        $stmt->bindValue(':start_keyword', $keyword . '%');
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo word mới
     */
    public function create($data) {
        return parent::create($data);
    }
    
    /**
     * Cập nhật word
     */
    public function update($id, $data) {
        return parent::update($id, $data);
    }
    
    /**
     * Xóa word
     */
    public function delete($id) {
        return parent::delete($id);
    }
    
    /**
     * Import words từ file JSON
     */
    public function importFromJson($jsonData) {
        try {
            $this->conn->beginTransaction();
            
            $imported = 0;
            $errors = [];
            
            foreach ($jsonData as $wordData) {
                // Kiểm tra dữ liệu bắt buộc
                if (!isset($wordData['word']) || empty($wordData['word'])) {
                    $errors[] = "Word is required";
                    continue;
                }
                
                // Tạo word mới
                $wordId = $this->create([
                    'word' => $wordData['word'],
                    'pos' => $wordData['pos'] ?? null,
                    'phonetic' => $wordData['phonetic'] ?? null,
                    'phonetic_text' => $wordData['phonetic_text'] ?? null,
                    'phonetic_am' => $wordData['phonetic_am'] ?? null,
                    'phonetic_am_text' => $wordData['phonetic_am_text'] ?? null
                ]);
                
                if (!$wordId) {
                    $errors[] = "Failed to create word: {$wordData['word']}";
                    continue;
                }
                
                // Xử lý senses nếu có
                if (isset($wordData['senses']) && is_array($wordData['senses'])) {
                    $senseModel = new SenseModel($this->conn);
                    
                    foreach ($wordData['senses'] as $senseData) {
                        if (!isset($senseData['definition']) || empty($senseData['definition'])) {
                            $errors[] = "Definition is required for word: {$wordData['word']}";
                            continue;
                        }
                        
                        // Tạo sense mới
                        $senseId = $senseModel->create([
                            'word_id' => $wordId,
                            'definition' => $senseData['definition']
                        ]);
                        
                        if (!$senseId) {
                            $errors[] = "Failed to create sense for word: {$wordData['word']}";
                            continue;
                        }
                        
                        // Xử lý examples nếu có
                        if (isset($senseData['examples']) && is_array($senseData['examples'])) {
                            $exampleModel = new ExampleModel($this->conn);
                            
                            foreach ($senseData['examples'] as $exampleData) {
                                if (!isset($exampleData['x']) || empty($exampleData['x'])) {
                                    $errors[] = "Example text is required for word: {$wordData['word']}";
                                    continue;
                                }
                                
                                // Tạo example mới
                                $exampleId = $exampleModel->create([
                                    'sense_id' => $senseId,
                                    'cf' => $exampleData['cf'] ?? null,
                                    'x' => $exampleData['x']
                                ]);
                                
                                if (!$exampleId) {
                                    $errors[] = "Failed to create example for word: {$wordData['word']}";
                                }
                            }
                        }
                    }
                }
                
                $imported++;
            }
            
            if (empty($errors)) {
                $this->conn->commit();
                return [
                    'success' => true,
                    'imported' => $imported,
                    'message' => "Successfully imported {$imported} words"
                ];
            } else {
                $this->conn->rollBack();
                return [
                    'success' => false,
                    'imported' => $imported,
                    'errors' => $errors
                ];
            }
        } catch (\Exception $e) {
            $this->conn->rollBack();
            error_log("Import Words Error: " . $e->getMessage());
            return [
                'success' => false,
                'imported' => $imported,
                'errors' => [$e->getMessage()]
            ];
        }
    }
} 