<?php
namespace App\Controllers;

use App\Services\WordService;
use App\Middleware\AuthMiddleware;

class WordController {
    private $wordService;
    private $authMiddleware;
    
    public function __construct() {
        $this->wordService = new WordService();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy danh sách tất cả words
     */
    public function getAll() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $words = $this->wordService->getAllWords();
            return ['words' => $words];
        } catch (\Exception $e) {
            error_log("GetAll Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách words'];
        }
    }
    
    /**
     * Lấy danh sách words với phân trang
     */
    public function getAllPaginated($page = 1, $limit = 10) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $result = $this->wordService->getAllWordsPaginated($page, $limit);
            return $result;
        } catch (\Exception $e) {
            error_log("GetAll Paginated Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách words'];
        }
    }
    
    /**
     * Lấy word theo ID
     */
    public function getById($wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $word = $this->wordService->getWordById($wordId);
            return ['word' => $word];
        } catch (\Exception $e) {
            error_log("GetById Word Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy word') {
                http_response_code(404);
            } else {
                http_response_code(500);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy words theo lesson ID
     */
    public function getByLessonId($lessonId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $words = $this->wordService->getWordsByLessonId($lessonId);
            return ['words' => $words];
        } catch (\Exception $e) {
            error_log("GetByLessonId Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách words'];
        }
    }
    
    /**
     * Tìm kiếm words
     */
    public function search($keyword) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $words = $this->wordService->searchWords($keyword);
            return ['words' => $words];
        } catch (\Exception $e) {
            error_log("Search Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể tìm kiếm words'];
        }
    }
    
    /**
     * Tạo word mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['word']) || empty($data['word'])) {
            http_response_code(400);
            return ['error' => 'Word là bắt buộc'];
        }

        try {
            $word = $this->wordService->createWord($data);
            
            http_response_code(201);
            return [
                'message' => 'Tạo word thành công',
                'word' => $word
            ];
        } catch (\Exception $e) {
            error_log("Create Word Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật word
     */
    public function update($wordId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $word = $this->wordService->updateWord($wordId, $data);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'word' => $word
            ];
        } catch (\Exception $e) {
            error_log("Update Word Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy word') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa word
     */
    public function delete($wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $this->wordService->deleteWord($wordId);
            return ['message' => 'Xóa word thành công'];
        } catch (\Exception $e) {
            error_log("Delete Word Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy word') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Import words từ JSON
     */
    public function import() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }

        // Kiểm tra file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            return ['error' => 'Không tìm thấy file upload'];
        }

        try {
            // Đọc nội dung file JSON
            $jsonContent = file_get_contents($_FILES['file']['tmp_name']);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format');
            }

            $result = $this->wordService->importWords($data);
            
            return [
                'message' => 'Import thành công',
                'result' => $result
            ];
        } catch (\Exception $e) {
            error_log("Import Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
} 