<?php
namespace App\Services;

use App\Config\Database;
use App\Models\CategoryModel;

class CategoryService {
    private $categoryModel;
    
    public function __construct() {
        $db = new Database();
        $this->categoryModel = new CategoryModel($db->connect());
    }
    
    /**
     * Lấy danh sách tất cả categories
     */
    public function getAllCategories() {
        try {
            return $this->categoryModel->getAll();
        } catch (\Exception $e) {
            error_log("GetAllCategories Error: " . $e->getMessage());
            throw new \Exception('Không thể lấy danh sách categories');
        }
    }
    
    /**
     * Lấy category theo ID
     */
    public function getCategoryById($id) {
        try {
            $category = $this->categoryModel->getById($id);
            
            if (!$category) {
                throw new \Exception('Không tìm thấy category');
            }
            
            return $category;
        } catch (\Exception $e) {
            error_log("GetCategoryById Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Tạo category mới
     */
    public function createCategory($data) {
        try {
            // Validate dữ liệu
            if (!isset($data['title']) || empty($data['title'])) {
                throw new \Exception('Tiêu đề là bắt buộc');
            }
            
            // Chuẩn bị dữ liệu
            $categoryData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'order_index' => $data['order_index'] ?? 0
            ];
            
            // Tạo category
            $categoryId = $this->categoryModel->create($categoryData);
            
            if (!$categoryId) {
                throw new \Exception('Không thể tạo category');
            }
            
            // Lấy thông tin category vừa tạo
            return $this->getCategoryById($categoryId);
        } catch (\Exception $e) {
            error_log("CreateCategory Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cập nhật category
     */
    public function updateCategory($id, $data) {
        try {
            // Kiểm tra category tồn tại
            $category = $this->getCategoryById($id);
            
            // Chuẩn bị dữ liệu cập nhật
            $updateData = [];
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }
            if (isset($data['order_index'])) {
                $updateData['order_index'] = $data['order_index'];
            }
            
            // Cập nhật category
            $updated = $this->categoryModel->update($id, $updateData);
            
            if (!$updated) {
                throw new \Exception('Không thể cập nhật category');
            }
            
            // Lấy thông tin category sau khi cập nhật
            return $this->getCategoryById($id);
        } catch (\Exception $e) {
            error_log("UpdateCategory Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Xóa category
     */
    public function deleteCategory($id) {
        try {
            // Kiểm tra category tồn tại
            $category = $this->getCategoryById($id);
            
            // Xóa category
            $deleted = $this->categoryModel->delete($id);
            
            if (!$deleted) {
                throw new \Exception('Không thể xóa category');
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("DeleteCategory Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cập nhật tiến độ của category
     */
    public function updateCategoryProgress($id) {
        try {
            // Kiểm tra category tồn tại
            $category = $this->getCategoryById($id);
            
            // Cập nhật tiến độ
            $updated = $this->categoryModel->updateProgress($id);
            
            if (!$updated) {
                throw new \Exception('Không thể cập nhật tiến độ category');
            }
            
            // Lấy thông tin category sau khi cập nhật
            return $this->getCategoryById($id);
        } catch (\Exception $e) {
            error_log("UpdateCategoryProgress Error: " . $e->getMessage());
            throw $e;
        }
    }
} 