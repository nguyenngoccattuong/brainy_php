<?php
namespace App\Controllers;

use App\Services\LearnService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

class LearnController {
    private $learnService;
    private $authMiddleware;
    
    public function __construct() {
        $this->learnService = new LearnService();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy danh sách trạng thái học của người dùng
     * 
     * @return array
     */
    public function getAll() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy user ID từ sub trong token hoặc id nếu có
        $userId = $auth['sub'] ?? ($auth['id'] ?? null);
        
        // Kiểm tra có ID người dùng không
        if (!$userId) {
            return Response::error('Token không hợp lệ hoặc không chứa ID người dùng', 401);
        }
        
        // Lấy tham số phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        try {
            $learn = $this->learnService->getLearnByUserId($userId, $page, $limit);
            return Response::success(['learn' => $learn], 'Lấy danh sách trạng thái học thành công');
        } catch (\Exception $e) {
            error_log("GetAll Learn Error: " . $e->getMessage());
            return Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Lấy danh sách trạng thái học của người dùng theo status
     * 
     * @return array
     */
    public function getByStatus() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy user ID từ sub trong token hoặc id nếu có
        $userId = $auth['sub'] ?? ($auth['id'] ?? null);
        
        // Kiểm tra có ID người dùng không
        if (!$userId) {
            return Response::error('Token không hợp lệ hoặc không chứa ID người dùng', 401);
        }
        
        // Lấy tham số
        $status = $_GET['status'] ?? null;
        if (!$status) {
            return Response::error('Status là bắt buộc', 400);
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        try {
            $learn = $this->learnService->getLearnByStatus($userId, $status, $page, $limit);
            return Response::success(['learn' => $learn], 'Lấy danh sách trạng thái học thành công');
        } catch (\Exception $e) {
            error_log("GetByStatus Learn Error: " . $e->getMessage());
            return Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Lấy trạng thái học cụ thể của một từ
     * 
     * @param string $wordId UUID của từ
     * @return array
     */
    public function getByWordId($wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy user ID từ sub trong token hoặc id nếu có
        $userId = $auth['sub'] ?? ($auth['id'] ?? null);
        
        // Kiểm tra có ID người dùng không
        if (!$userId) {
            return Response::error('Token không hợp lệ hoặc không chứa ID người dùng', 401);
        }
        
        try {
            $learn = $this->learnService->getLearnStatus($userId, $wordId);
            return Response::success(['learn' => $learn], 'Lấy trạng thái học thành công');
        } catch (\Exception $e) {
            error_log("GetByWordId Learn Error: " . $e->getMessage());
            
            if ($e->getMessage() === 'Không tìm thấy thông tin học từ này') {
                return Response::notFound($e->getMessage());
            }
            
            return Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Tạo mới trạng thái học
     * 
     * @param array $data Dữ liệu tạo mới
     * @return array
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy user ID từ sub trong token hoặc id nếu có
        $userId = $auth['sub'] ?? ($auth['id'] ?? null);
        
        // Kiểm tra có ID người dùng không
        if (!$userId) {
            return Response::error('Token không hợp lệ hoặc không chứa ID người dùng', 401);
        }
        
        // Kiểm tra dữ liệu
        if (!isset($data['word_id']) || empty($data['word_id'])) {
            return Response::error('Word ID là bắt buộc', 400);
        }
        
        if (!isset($data['status']) || empty($data['status'])) {
            // Nếu không cung cấp status, mặc định là "learning"
            $data['status'] = 'learning';
        }
        
        try {
            $learn = $this->learnService->updateLearnStatus($userId, $data['word_id'], $data['status']);
            return Response::created(['learn' => $learn], 'Tạo trạng thái học thành công');
        } catch (\Exception $e) {
            error_log("Create Learn Error: " . $e->getMessage());
            return Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Cập nhật trạng thái học
     * 
     * @param string $wordId UUID của từ
     * @param array $data Dữ liệu cập nhật
     * @return array
     */
    public function update($wordId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy user ID từ sub trong token hoặc id nếu có
        $userId = $auth['sub'] ?? ($auth['id'] ?? null);
        
        // Kiểm tra có ID người dùng không
        if (!$userId) {
            return Response::error('Token không hợp lệ hoặc không chứa ID người dùng', 401);
        }
        
        // Kiểm tra dữ liệu
        if (!isset($data['status']) || empty($data['status'])) {
            return Response::error('Status là bắt buộc', 400);
        }
        
        try {
            $learn = $this->learnService->updateLearnStatus($userId, $wordId, $data['status']);
            return Response::success(['learn' => $learn], 'Cập nhật trạng thái học thành công');
        } catch (\Exception $e) {
            error_log("Update Learn Error: " . $e->getMessage());
            return Response::error($e->getMessage(), 400);
        }
    }
    
    /**
     * Xóa trạng thái học
     * 
     * @param string $wordId UUID của từ
     * @return array
     */
    public function delete($wordId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Lấy user ID từ sub trong token hoặc id nếu có
        $userId = $auth['sub'] ?? ($auth['id'] ?? null);
        
        // Kiểm tra có ID người dùng không
        if (!$userId) {
            return Response::error('Token không hợp lệ hoặc không chứa ID người dùng', 401);
        }
        
        try {
            $this->learnService->deleteLearnStatus($userId, $wordId);
            return Response::success(null, 'Xóa trạng thái học thành công');
        } catch (\Exception $e) {
            error_log("Delete Learn Error: " . $e->getMessage());
            
            if ($e->getMessage() === 'Không tìm thấy thông tin học từ này') {
                return Response::notFound($e->getMessage());
            }
            
            return Response::error($e->getMessage(), 400);
        }
    }
} 