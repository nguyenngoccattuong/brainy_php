<?php
namespace App\Controllers;

use App\Services\CategoryService;
use App\Middleware\AuthMiddleware;

class CategoryController {
    private $categoryService;
    private $authMiddleware;
    
    public function __construct() {
        $this->categoryService = new CategoryService();
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy danh sách tất cả categories
     */
    public function getAll() {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $categories = $this->categoryService->getAllCategories();
            return ['categories' => $categories];
        } catch (\Exception $e) {
            error_log("GetAll Categories Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách categories'];
        }
    }
    
    /**
     * Lấy category theo ID
     */
    public function getById($categoryId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $category = $this->categoryService->getCategoryById($categoryId);
            return ['category' => $category];
        } catch (\Exception $e) {
            error_log("GetById Category Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy category') {
                http_response_code(404);
            } else {
                http_response_code(500);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Tạo category mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['title']) || empty($data['title'])) {
            http_response_code(400);
            return ['error' => 'Tiêu đề là bắt buộc'];
        }

        if (!isset($data['total']) || !is_numeric($data['total'])) {
            $data['total'] = 0; // Set default total to 0
        }
        
        try {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Creating category with data: " . json_encode($data));
            }

            $category = $this->categoryService->createCategory($data);
            
            http_response_code(201);
            return [
                'message' => 'Tạo category thành công',
                'category' => $category
            ];
        } catch (\Exception $e) {
            error_log("Create Category Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật category
     */
    public function update($categoryId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $category = $this->categoryService->updateCategory($categoryId, $data);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'category' => $category
            ];
        } catch (\Exception $e) {
            error_log("Update Category Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy category') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa category
     */
    public function delete($categoryId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $this->categoryService->deleteCategory($categoryId);
            return ['message' => 'Xóa category thành công'];
        } catch (\Exception $e) {
            error_log("Delete Category Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy category') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            return ['error' => $e->getMessage()];
        }
    }
} 