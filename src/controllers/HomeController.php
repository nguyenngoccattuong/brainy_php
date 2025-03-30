<?php
namespace App\Controllers;

/**
 * Class HomeController
 * Controller xử lý trang chủ API
 */
class HomeController {
    /**
     * Phương thức trang chủ
     * 
     * @param array $data Dữ liệu từ request (không sử dụng)
     * @return array
     */
    public function index($data = []) {
        return [
            'name' => 'Brainy API',
            'version' => '1.0.0',
            'description' => 'API for vocabulary learning application',
            'documentation' => 'Import postman_collection.json to view all endpoints',
            'status' => 'running'
        ];
    }
} 