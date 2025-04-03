<?php
namespace App\Controllers;

use App\Services\CategoryService;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

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
            return Response::unauthorized();
        }
        
        try {
            $categories = $this->categoryService->getAllCategories();
            return Response::success(['categories' => $categories], 'Lấy danh sách categories thành công');
        } catch (\Exception $e) {
            error_log("GetAll Categories Error: " . $e->getMessage());
            return Response::serverError('Không thể lấy danh sách categories');
        }
    }
    
    /**
     * Lấy category theo ID
     */
    public function getById($categoryId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $category = $this->categoryService->getCategoryById($categoryId);
            return Response::success(['category' => $category], 'Lấy thông tin category thành công');
        } catch (\Exception $e) {
            error_log("GetById Category Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy category') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::serverError($e->getMessage());
            }
        }
    }
    
    /**
     * Tạo category mới
     */
    public function create($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        // Kiểm tra dữ liệu đầu vào
        if (!isset($data['title']) || empty($data['title'])) {
            return Response::error('Tiêu đề là bắt buộc', 400);
        }
        
        try {
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Creating category with data: " . json_encode($data));
            }

            $category = $this->categoryService->createCategory($data);
            
            return Response::created(['category' => $category], 'Tạo category thành công');
        } catch (\Exception $e) {
            error_log("Create Category Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Cập nhật category
     */
    public function update($categoryId, $data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $category = $this->categoryService->updateCategory($categoryId, $data);
            
            return Response::success(['category' => $category], 'Cập nhật thông tin thành công');
        } catch (\Exception $e) {
            error_log("Update Category Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy category') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::error($e->getMessage(), 400);
            }
        }
    }
    
    /**
     * Xóa category
     */
    public function delete($categoryId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            return Response::unauthorized();
        }
        
        try {
            $this->categoryService->deleteCategory($categoryId);
            return Response::success(null, 'Xóa category thành công');
        } catch (\Exception $e) {
            error_log("Delete Category Error: " . $e->getMessage());
            if ($e->getMessage() === 'Không tìm thấy category') {
                return Response::notFound($e->getMessage());
            } else {
                return Response::error($e->getMessage(), 400);
            }
        }
    }
} 