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
        // Lấy danh sách các từ
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
        $words = $stmt->fetchAll();
        
        // Lấy thêm senses và examples cho mỗi từ
        $senseModel = new SenseModel($this->conn);
        $exampleModel = new ExampleModel($this->conn);
        
        foreach ($words as &$word) {
            // Lấy senses
            $senses = $senseModel->getByWordId($word['id']);
            
            // Lấy examples cho mỗi sense
            foreach ($senses as &$sense) {
                $sense['examples'] = $exampleModel->getBySenseId($sense['id']);
            }
            
            $word['senses'] = $senses;
        }
        
        return $words;
    }
    
    /**
     * Lấy word theo ID
     */
    public function getById($id) {
        // Lấy thông tin từ
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
        $word = $stmt->fetch();
        
        if (!$word) {
            return false;
        }
        
        // Lấy senses và examples
        $senseModel = new SenseModel($this->conn);
        $exampleModel = new ExampleModel($this->conn);
        
        // Lấy senses
        $senses = $senseModel->getByWordId($word['id']);
        
        // Lấy examples cho mỗi sense
        foreach ($senses as &$sense) {
            $sense['examples'] = $exampleModel->getBySenseId($sense['id']);
        }
        
        $word['senses'] = $senses;
        
        return $word;
    }
    
    /**
     * Lấy words theo lesson_id
     */
    public function getByLessonId($lessonId) {
        // Lấy danh sách từ theo lesson
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
        $words = $stmt->fetchAll();
        
        // Lấy thêm senses và examples cho mỗi từ
        $senseModel = new SenseModel($this->conn);
        $exampleModel = new ExampleModel($this->conn);
        
        foreach ($words as &$word) {
            // Lấy senses
            $senses = $senseModel->getByWordId($word['id']);
            
            // Lấy examples cho mỗi sense
            foreach ($senses as &$sense) {
                $sense['examples'] = $exampleModel->getBySenseId($sense['id']);
            }
            
            $word['senses'] = $senses;
        }
        
        return $words;
    }
    
    /**
     * Tìm kiếm words
     */
    public function search($keyword) {
        // Chuẩn bị từ khóa tìm kiếm với LIKE
        $likeKeyword = "%{$keyword}%";
        
        // Tìm kiếm từ vựng chỉ trong trường word
        $sql = "SELECT DISTINCT w.id 
                FROM {$this->table} w 
                WHERE w.word LIKE :word_like";
                    
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':word_like', $likeKeyword);
        $stmt->execute();
        
        $wordIds = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        
        if (empty($wordIds)) {
            return [];
        }
        
        // Tạo danh sách từ vựng đầy đủ từ các ID tìm được
        $words = [];
        foreach ($wordIds as $wordId) {
            $word = $this->getById($wordId);
            if ($word) {
                $words[] = $word;
            }
        }
        
        // Sắp xếp kết quả sau khi đã lấy đầy đủ thông tin
        usort($words, function($a, $b) use ($keyword) {
            // Ưu tiên đúng từ khóa
            if ($a['word'] === $keyword && $b['word'] !== $keyword) {
                return -1;
            }
            if ($a['word'] !== $keyword && $b['word'] === $keyword) {
                return 1;
            }
            
            // Ưu tiên từ bắt đầu bằng từ khóa
            $aStartsWith = strpos($a['word'], $keyword) === 0;
            $bStartsWith = strpos($b['word'], $keyword) === 0;
            if ($aStartsWith && !$bStartsWith) {
                return -1;
            }
            if (!$aStartsWith && $bStartsWith) {
                return 1;
            }
            
            // Sắp xếp theo bảng chữ cái
            return strcmp($a['word'], $b['word']);
        });
        
        return $words;
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