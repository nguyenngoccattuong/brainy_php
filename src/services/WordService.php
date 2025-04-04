<?php
namespace App\Services;

use App\Config\Database;
use App\Models\WordModel;
use App\Models\SenseModel;
use App\Models\ExampleModel;
use App\Controllers\CloudinaryController;

class WordService {
    private $wordModel;
    private $senseModel;
    private $exampleModel;
    private $cloudinaryController;
    
    public function __construct() {
        $db = new Database();
        $connection = $db->connect();
        $this->wordModel = new WordModel($connection);
        $this->senseModel = new SenseModel($connection);
        $this->exampleModel = new ExampleModel($connection);
        $this->cloudinaryController = new CloudinaryController();
    }
    
    /**
     * Lấy tất cả words
     */
    public function getAllWords() {
        return $this->wordModel->getAll();
    }

    /**
     * Lấy words với phân trang
     */
    public function getAllWordsPaginated($page = 1, $limit = 10) {
        return $this->wordModel->getAllPaginated($page, $limit);
    }
    
    /**
     * Lấy word theo ID
     */
    public function getWordById($wordId) {
        $word = $this->wordModel->getById($wordId);
        if (!$word) {
            throw new \Exception('Không tìm thấy word');
        }
        return $word;
    }
    
    /**
     * Lấy words theo lesson ID
     */
    public function getWordsByLessonId($lessonId) {
        return $this->wordModel->getByLessonId($lessonId);
    }
    
    /**
     * Tìm kiếm words
     */
    public function searchWords($keyword) {
        return $this->wordModel->search($keyword);
    }
    
    /**
     * Tạo word mới
     */
    public function createWord($data) {
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("Creating word with data: " . json_encode($data));
        }

        // Create a copy of the data without senses field to avoid SQL issues
        $wordData = $data;
        if (isset($wordData['senses'])) {
            $senses = $wordData['senses'];
            unset($wordData['senses']);
        } else {
            $senses = [];
        }

        // Xử lý upload audio nếu có
        if (isset($wordData['audio']) && !empty($wordData['audio'])) {
            $uploadResult = $this->cloudinaryController->upload([
                'file' => $wordData['audio'],
                'owner_type' => 'Word',
                'owner_id' => 'temp'
            ]);

            if (isset($uploadResult['error'])) {
                throw new \Exception('Không thể upload audio: ' . $uploadResult['error']);
            }

            $wordData['audio_id'] = $uploadResult['file']['id'];
        }

        // Xử lý upload image nếu có
        if (isset($wordData['image']) && !empty($wordData['image'])) {
            $uploadResult = $this->cloudinaryController->upload([
                'file' => $wordData['image'],
                'owner_type' => 'Word',
                'owner_id' => 'temp'
            ]);

            if (isset($uploadResult['error'])) {
                throw new \Exception('Không thể upload image: ' . $uploadResult['error']);
            }

            $wordData['image_id'] = $uploadResult['file']['id'];
        }

        // Tạo word
        $wordId = $this->wordModel->create($wordData);
        if (!$wordId) {
            throw new \Exception('Không thể tạo word');
        }

        // Cập nhật owner_id cho các file đã upload
        if (isset($wordData['audio_id'])) {
            $this->cloudinaryController->upload([
                'file_id' => $wordData['audio_id'],
                'owner_id' => $wordId
            ]);
        }
        if (isset($wordData['image_id'])) {
            $this->cloudinaryController->upload([
                'file_id' => $wordData['image_id'],
                'owner_id' => $wordId
            ]);
        }

        // Tạo senses và examples nếu có
        if (!empty($senses) && is_array($senses)) {
            foreach ($senses as $senseData) {
                $senseData['word_id'] = $wordId;
                $senseId = $this->senseModel->create($senseData);

                if (isset($senseData['examples']) && is_array($senseData['examples'])) {
                    foreach ($senseData['examples'] as $exampleData) {
                        $exampleData['sense_id'] = $senseId;
                        $this->exampleModel->create($exampleData);
                    }
                }
            }
        }

        return $this->wordModel->getById($wordId);
    }
    
    /**
     * Cập nhật word
     */
    public function updateWord($wordId, $data) {
        // Kiểm tra word tồn tại
        $word = $this->wordModel->getById($wordId);
        if (!$word) {
            throw new \Exception('Không tìm thấy word');
        }

        // Xử lý upload audio mới nếu có
        if (isset($data['audio']) && !empty($data['audio'])) {
            $uploadResult = $this->cloudinaryController->upload([
                'file' => $data['audio'],
                'owner_type' => 'Word',
                'owner_id' => $wordId
            ]);

            if (isset($uploadResult['error'])) {
                throw new \Exception('Không thể upload audio: ' . $uploadResult['error']);
            }

            $data['audio_id'] = $uploadResult['file']['id'];

            // Xóa audio cũ nếu có
            if ($word['audio_id']) {
                $this->cloudinaryController->delete($word['audio_id']);
            }
        }

        // Xử lý upload image mới nếu có
        if (isset($data['image']) && !empty($data['image'])) {
            $uploadResult = $this->cloudinaryController->upload([
                'file' => $data['image'],
                'owner_type' => 'Word',
                'owner_id' => $wordId
            ]);

            if (isset($uploadResult['error'])) {
                throw new \Exception('Không thể upload image: ' . $uploadResult['error']);
            }

            $data['image_id'] = $uploadResult['file']['id'];

            // Xóa image cũ nếu có
            if ($word['image_id']) {
                $this->cloudinaryController->delete($word['image_id']);
            }
        }

        // Cập nhật thông tin word
        $updated = $this->wordModel->update($wordId, $data);
        if (!$updated) {
            throw new \Exception('Không thể cập nhật thông tin');
        }

        return $this->wordModel->getById($wordId);
    }
    
    /**
     * Xóa word
     */
    public function deleteWord($wordId) {
        // Kiểm tra word tồn tại
        $word = $this->wordModel->getById($wordId);
        if (!$word) {
            throw new \Exception('Không tìm thấy word');
        }

        // Xóa các file trên Cloudinary
        if ($word['audio_id']) {
            $this->cloudinaryController->delete($word['audio_id']);
        }
        if ($word['image_id']) {
            $this->cloudinaryController->delete($word['image_id']);
        }

        // Xóa word (senses và examples sẽ tự động bị xóa do foreign key cascade)
        $deleted = $this->wordModel->delete($wordId);
        if (!$deleted) {
            throw new \Exception('Không thể xóa word');
        }

        return true;
    }

    /**
     * Import words từ JSON
     */
    public function importWords($jsonData) {
        try {
            $imported = 0;
            $errors = [];
            $result = [];

            foreach ($jsonData as $wordData) {
                try {
                    // Kiểm tra dữ liệu bắt buộc
                    if (!isset($wordData['word']) || empty($wordData['word'])) {
                        $errors[] = "Word is required";
                        continue;
                    }
                    
                    // Chuẩn bị dữ liệu cho word
                    $newWordData = [
                        'word' => $wordData['word'],
                        'pos' => $wordData['pos'] ?? null,
                        'phonetic' => $wordData['phonetic'] ?? null,
                        'phonetic_text' => $wordData['phonetic_text'] ?? null,
                        'phonetic_am' => $wordData['phonetic_am'] ?? null,
                        'phonetic_am_text' => $wordData['phonetic_am_text'] ?? null
                    ];
                    
                    // Tạo word mới
                    $wordId = $this->wordModel->create($newWordData);
                    
                    if (!$wordId) {
                        $errors[] = "Failed to create word: {$wordData['word']}";
                        continue;
                    }
                    
                    // Xử lý senses nếu có
                    if (isset($wordData['senses']) && is_array($wordData['senses'])) {
                        foreach ($wordData['senses'] as $senseData) {
                            if (!isset($senseData['definition']) || empty($senseData['definition'])) {
                                $errors[] = "Definition is required for word: {$wordData['word']}";
                                continue;
                            }
                            
                            // Tạo sense mới
                            $senseId = $this->senseModel->create([
                                'word_id' => $wordId,
                                'definition' => $senseData['definition']
                            ]);
                            
                            if (!$senseId) {
                                $errors[] = "Failed to create sense for word: {$wordData['word']}";
                                continue;
                            }
                            
                            // Xử lý examples nếu có
                            if (isset($senseData['examples']) && is_array($senseData['examples'])) {
                                foreach ($senseData['examples'] as $exampleData) {
                                    if (!isset($exampleData['x']) || empty($exampleData['x'])) {
                                        $errors[] = "Example text is required for word: {$wordData['word']}";
                                        continue;
                                    }
                                    
                                    // Tạo example mới
                                    $exampleId = $this->exampleModel->create([
                                        'sense_id' => $senseId,
                                        'cf' => $exampleData['cf'] ?? '',
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
                    $result[] = $this->getWordById($wordId);
                } catch (\Exception $e) {
                    $errors[] = "Error importing word '{$wordData['word']}': " . $e->getMessage();
                }
            }

            return [
                'success' => $imported > 0,
                'imported' => $imported,
                'errors' => $errors,
                'words' => $result
            ];
        } catch (\Exception $e) {
            error_log("Import Words Error: " . $e->getMessage());
            throw $e;
        }
    }
} 