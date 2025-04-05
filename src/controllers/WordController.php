<?php
namespace App\Controllers;

use App\Services\WordService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

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
            return Response::unauthorized();
        }
        
        try {
            $words = $this->wordService->getAllWords();
            return Response::success($words);
        } catch (\Exception $e) {
            error_log("GetAll Words Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy danh sách words');
        }
    }
    
    /**
     * Lấy danh sách words với phân trang
     */
    public function getAllPaginated($page = 1, $limit = 10) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Đảm bảo $page và $limit là số nguyên
        if (is_array($page)) {
            // Nếu nhận được array (từ query params), lấy tham số "page" nếu có
            $page = isset($page['page']) ? (int)$page['page'] : 1;
        } else {
            $page = (int)$page;
        }
        
        if (is_array($limit)) {
            // Nếu nhận được array, lấy tham số "limit" nếu có
            $limit = isset($limit['limit']) ? (int)$limit['limit'] : 10;
        } else {
            $limit = (int)$limit;
        }
        
        // Kiểm tra và đặt giá trị mặc định nếu không hợp lệ
        $page = ($page < 1) ? 1 : $page;
        $limit = ($limit < 1 || $limit > 100) ? 10 : $limit;
        
        try {
            $result = $this->wordService->getAllWordsPaginated($page, $limit);
            return Response::success($result);
        } catch (\Exception $e) {
            error_log("GetAll Paginated Words Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy danh sách words');
        }
    }
    
    /**
     * Lấy word theo ID
     */
    public function getById($wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $word = $this->wordService->getWordById($wordId);
            return Response::success($word);
        } catch (\Exception $e) {
            error_log("GetById Word Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy word') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::serverError($e->getMessage());
            }
        }
    }
    
    /**
     * Lấy words theo lesson ID
     */
    public function getByLessonId($lessonId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $words = $this->wordService->getWordsByLessonId($lessonId);
            return Response::success($words);
        } catch (\Exception $e) {
            error_log("GetByLessonId Words Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy danh sách words');
        }
    }
    
    /**
     * Tìm kiếm words
     */
    public function search() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy từ khóa tìm kiếm từ query parameters
        $keyword = $_GET['keyword'] ?? '';
        
        if (empty($keyword)) {
            return Response::error('Từ khóa tìm kiếm không được để trống', 400);
        }
        
        try {
            $words = $this->wordService->searchWords($keyword);
            return Response::success($words);
        } catch (\Exception $e) {
            error_log("Search Words Error: " . $e->getMessage());
            return Response::serverError('Không thể tìm kiếm words');
        }
    }
    
    /**
     * Tạo word mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['word']) || empty($data['word'])) {
            return Response::error('Word là bắt buộc', 400);
        }

        try {
            $word = $this->wordService->createWord($data);
            return Response::created($word, 'Tạo word thành công');
        } catch (\Exception $e) {
            error_log("Create Word Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return Response::error($e->getMessage(), 500);
        }
    }
    
    /**
     * Cập nhật word
     */
    public function update($wordId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $word = $this->wordService->updateWord($wordId, $data);
            return Response::success($word, 'Cập nhật thông tin thành công');
        } catch (\Exception $e) {
            error_log("Update Word Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy word') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::error($e->getMessage(), 400);
            }
        }
    }
    
    /**
     * Xóa word
     */
    public function delete($wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $this->wordService->deleteWord($wordId);
            return Response::success(null, 'Xóa word thành công');
        } catch (\Exception $e) {
            error_log("Delete Word Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy word') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::error($e->getMessage(), 400);
            }
        }
    }

    /**
     * Import words từ JSON
     */
    public function import() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }

        // Kiểm tra có dữ liệu được gửi lên không
        $inputJSON = file_get_contents('php://input');
        $data = json_decode($inputJSON, true);
        
        // Nếu không có dữ liệu JSON trực tiếp
        if ($data === null) {
            return Response::error('Không tìm thấy dữ liệu JSON hợp lệ', 400);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return Response::error('Invalid JSON format: ' . json_last_error_msg(), 400);
        }

        try {
            $result = $this->wordService->importWords($data);
            return Response::success($result, 'Import thành công');
        } catch (\Exception $e) {
            error_log("Import Words Error: " . $e->getMessage());
            return Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Import words từ file JSON upload
     */
    public function importFromFile() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }

        // Kiểm tra file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return Response::error('Không tìm thấy file upload', 400);
        }

        try {
            // Đọc nội dung file JSON
            $jsonContent = file_get_contents($_FILES['file']['tmp_name']);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return Response::error('Invalid JSON format: ' . json_last_error_msg(), 400);
            }

            $result = $this->wordService->importWords($data);
            return Response::success($result, 'Import thành công');
        } catch (\Exception $e) {
            error_log("Import Words Error: " . $e->getMessage());
            return Response::serverError($e->getMessage());
        }
    }

    /**
     * Lấy danh sách words ngẫu nhiên
     */
    public function getRandom() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy tham số limit từ query parameters, mặc định là 5
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        try {
            $words = $this->wordService->getRandomWords($limit);
            
            if (empty($words)) {
                return Response::success(['words' => []], 'Không có từ vựng nào');
            }
            
            return Response::success(['words' => $words], 'Lấy danh sách từ vựng ngẫu nhiên thành công');
        } catch (\Exception $e) {
            error_log("GetRandom Words Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy danh sách từ vựng ngẫu nhiên');
        }
    }
} 