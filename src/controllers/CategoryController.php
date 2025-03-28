<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\CategoryService;
use App\Middleware\AuthMiddleware;

/**
 * Class CategoryController
 * Controller xử lý các request liên quan đến danh mục
 */
class CategoryController {
    private $categoryService;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->categoryService = new \App\Services\CategoryService($db->connect());
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Lấy tất cả danh mục
     * 
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getAll($data = []) {
        try {
            $categories = $this->categoryService->getAllCategories();
            return ['categories' => $categories];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Lấy thông tin danh mục theo ID
     * 
     * @param string $id UUID của danh mục
     * @param array $data Dữ liệu request
     * @return array
     */
    public function getById($id, $data = []) {
        try {
            $category = $this->categoryService->getCategoryById($id);
            
            if (!$category) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy danh mục'];
            }
            
            return ['category' => $category];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Tạo danh mục mới
     * 
     * @param array $data Dữ liệu danh mục
     * @return array
     */
    public function create($data) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['title']) || empty($data['title'])) {
            http_response_code(400);
            return ['error' => 'Tiêu đề là bắt buộc'];
        }
        
        try {
            $newCategory = $this->categoryService->createCategory($data);
            
            if (!$newCategory) {
                http_response_code(500);
                return ['error' => 'Không thể tạo danh mục mới'];
            }
            
            http_response_code(201);
            return ['category' => $newCategory];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Cập nhật thông tin danh mục
     * 
     * @param string $id UUID của danh mục
     * @param array $data Dữ liệu cần cập nhật
     * @return array
     */
    public function update($id, $data) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Kiểm tra xem có dữ liệu để cập nhật không
        if (empty($data)) {
            http_response_code(400);
            return ['error' => 'Không có dữ liệu để cập nhật'];
        }
        
        try {
            $updatedCategory = $this->categoryService->updateCategory($id, $data);
            
            if (!$updatedCategory) {
                http_response_code(500);
                return ['error' => 'Không thể cập nhật thông tin danh mục'];
            }
            
            return ['category' => $updatedCategory];
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Không tìm thấy danh mục') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xóa danh mục
     * 
     * @param string $id UUID của danh mục
     * @param array $data Dữ liệu request
     * @return array
     */
    public function delete($id, $data = []) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        try {
            $deleted = $this->categoryService->deleteCategory($id);
            
            if (!$deleted) {
                http_response_code(500);
                return ['error' => 'Không thể xóa danh mục'];
            }
            
            return ['success' => true, 'message' => 'Xóa danh mục thành công'];
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Không tìm thấy danh mục') {
                http_response_code(404);
            } else {
                http_response_code(400);
            }
            
            return ['error' => $e->getMessage()];
        }
    }
} 