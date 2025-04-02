<?php
namespace App\Controllers;

use App\Config\Database;
use App\Models\CategoryModel;
use App\Middleware\AuthMiddleware;

class CategoryController {
    private $categoryModel;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $this->categoryModel = new CategoryModel($db->connect());
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
            $categories = $this->categoryModel->getAll();
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
            $category = $this->categoryModel->getById($categoryId);
            
            if (!$category) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy category'];
            }
            
            return ['category' => $category];
        } catch (\Exception $e) {
            error_log("GetById Category Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy thông tin category'];
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

            $categoryId = $this->categoryModel->create($data);
            
            if (!$categoryId) {
                http_response_code(400);
                return ['error' => 'Không thể tạo category'];
            }
            
            $category = $this->categoryModel->getById($categoryId);
            
            http_response_code(201);
            return [
                'message' => 'Tạo category thành công',
                'category' => $category
            ];
        } catch (\Exception $e) {
            error_log("Create Category Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            return ['error' => 'Không thể tạo category: ' . $e->getMessage()];
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
            // Kiểm tra category tồn tại
            $category = $this->categoryModel->getById($categoryId);
            if (!$category) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy category'];
            }
            
            // Cập nhật thông tin
            $updated = $this->categoryModel->update($categoryId, $data);
            
            if (!$updated) {
                http_response_code(400);
                return ['error' => 'Không thể cập nhật thông tin'];
            }
            
            $category = $this->categoryModel->getById($categoryId);
            
            return [
                'message' => 'Cập nhật thông tin thành công',
                'category' => $category
            ];
        } catch (\Exception $e) {
            error_log("Update Category Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể cập nhật thông tin'];
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
            // Kiểm tra category tồn tại
            $category = $this->categoryModel->getById($categoryId);
            if (!$category) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy category'];
            }
            
            // Xóa category
            $deleted = $this->categoryModel->delete($categoryId);
            
            if (!$deleted) {
                http_response_code(400);
                return ['error' => 'Không thể xóa category'];
            }
            
            return ['message' => 'Xóa category thành công'];
        } catch (\Exception $e) {
            error_log("Delete Category Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể xóa category'];
        }
    }
} 