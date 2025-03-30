<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\WordModel;
use App\Middleware\AuthMiddleware;

class WordController {
    private $wordModel;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->wordModel = new WordModel($db->connect());
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
            $words = $this->wordModel->getAll();
            return ['words' => $words];
        } catch (\Exception $e) {
            error_log("GetAll Words Error: " . $e->getMessage());
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
            $word = $this->wordModel->getById($wordId);
            
            if (!$word) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy word'];
            }
            
            return ['word' => $word];
        } catch (\Exception $e) {
            error_log("GetById Word Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy thông tin word'];
        }
    }
    
    /**
     * Lấy words theo lesson_id
     */
    public function getByLessonId($lessonId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $words = $this->wordModel->getByLessonId($lessonId);
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
    public function search($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $keyword = isset($data['keyword']) ? $data['keyword'] : '';
            
            if (empty($keyword)) {
                http_response_code(400);
                return ['error' => 'Từ khóa tìm kiếm là bắt buộc'];
            }
            
            $words = $this->wordModel->search($keyword);
            
            if (empty($words)) {
                // Không tìm thấy kết quả
                return [
                    'words' => [],
                    'message' => 'Không tìm thấy từ vựng nào phù hợp với từ khóa "' . $keyword . '"'
                ];
            }
            
            return ['words' => $words];
        } catch (\Exception $e) {
            error_log("Search Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể tìm kiếm words: ' . $e->getMessage()];
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
            return ['error' => 'Từ là bắt buộc'];
        }
        
        try {
            $wordId = $this->wordModel->create($data);
            
            if (!$wordId) {
                http_response_code(400);
                return ['error' => 'Không thể tạo word'];
            }
            
            $word = $this->wordModel->getById($wordId);
            
            http_response_code(201);
            return [
                'message' => 'Tạo word thành công',
                'word' => $word
            ];
        } catch (\Exception $e) {
            error_log("Create Word Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể tạo word'];
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
            // Kiểm tra word tồn tại
            $word = $this->wordModel->getById($wordId);
            if (!$word) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy word'];
            }
            
            // Cập nhật thông tin
            $updated = $this->wordModel->update($wordId, $data);
            
            if (!$updated) {
                http_response_code(400);
                return ['error' => 'Không thể cập nhật thông tin'];
            }
            
            $word = $this->wordModel->getById($wordId);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'word' => $word
            ];
        } catch (\Exception $e) {
            error_log("Update Word Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể cập nhật thông tin'];
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
            // Kiểm tra word tồn tại
            $word = $this->wordModel->getById($wordId);
            if (!$word) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy word'];
            }
            
            // Xóa word
            $deleted = $this->wordModel->delete($wordId);
            
            if (!$deleted) {
                http_response_code(400);
                return ['error' => 'Không thể xóa word'];
            }
            
            return ['message' => 'Xóa word thành công'];
        } catch (\Exception $e) {
            error_log("Delete Word Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể xóa word'];
        }
    }
    
    /**
     * Lấy danh sách tất cả words với phân trang
     */
    public function getAllPaginated($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $page = isset($data['page']) ? (int)$data['page'] : 1;
            $limit = isset($data['limit']) ? (int)$data['limit'] : 10;
            
            // Validate input
            if ($page < 1) {
                http_response_code(400);
                return ['error' => 'Page must be greater than 0'];
            }
            
            if ($limit < 1 || $limit > 100) {
                http_response_code(400);
                return ['error' => 'Limit must be between 1 and 100'];
            }
            
            $result = $this->wordModel->getAllPaginated($page, $limit);
            return $result;
        } catch (\Exception $e) {
            error_log("GetAllPaginated Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách words'];
        }
    }
    
    /**
     * Import words từ file JSON
     */
    public function importFromFile() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra file upload
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                return ['error' => 'No file uploaded or upload error'];
            }
            
            // Kiểm tra file type
            $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            if ($fileType !== 'json') {
                http_response_code(400);
                return ['error' => 'Only JSON files are allowed'];
            }
            
            // Đọc nội dung file
            $fileContent = file_get_contents($_FILES['file']['tmp_name']);
            $jsonData = json_decode($fileContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                return ['error' => 'Invalid JSON file: ' . json_last_error_msg()];
            }
            
            // Xác định định dạng dữ liệu và chuẩn hóa
            $wordsToImport = [];
            
            // Trường hợp 1: JSON là một mảng trực tiếp các từ vựng
            if (is_array($jsonData) && isset($jsonData[0]) && isset($jsonData[0]['word'])) {
                $wordsToImport = $jsonData;
            }
            // Trường hợp 2: JSON là một đối tượng có thuộc tính 'words'
            else if (isset($jsonData['words']) && is_array($jsonData['words'])) {
                $wordsToImport = $jsonData['words'];
            }
            // Không đúng định dạng
            else {
                http_response_code(400);
                return ['error' => 'Invalid data format: JSON must be an array of words or have a "words" array property'];
            }
            
            // Import dữ liệu
            $result = $this->wordModel->importFromJson($wordsToImport);
            
            if ($result['success']) {
                http_response_code(201);
                return $result;
            } else {
                http_response_code(400);
                return $result;
            }
        } catch (\Exception $e) {
            error_log("Import Words From File Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể import words từ file'];
        }
    }
    
    /**
     * Import words từ JSON data
     */
    public function import($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra dữ liệu đầu vào
            $wordsToImport = [];
            
            // Trường hợp 1: Dữ liệu là một mảng trực tiếp các từ vựng
            if (isset($data[0]) && isset($data[0]['word'])) {
                $wordsToImport = $data;
            }
            // Trường hợp 2: Dữ liệu có thuộc tính 'words'
            else if (isset($data['words']) && is_array($data['words'])) {
                $wordsToImport = $data['words'];
            }
            // Không đúng định dạng
            else {
                http_response_code(400);
                return ['error' => 'Invalid data format: JSON must be an array of words or have a "words" array property'];
            }
            
            // Import dữ liệu
            $result = $this->wordModel->importFromJson($wordsToImport);
            
            if ($result['success']) {
                http_response_code(201);
                return $result;
            } else {
                http_response_code(400);
                return $result;
            }
        } catch (\Exception $e) {
            error_log("Import Words Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể import words'];
        }
    }
} 