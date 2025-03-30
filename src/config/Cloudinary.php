<?php
namespace App\Config;

/**
 * Class Cloudinary
 * Cấu hình và quản lý kết nối đến Cloudinary
 */
class Cloudinary {
    private $cloudName;
    private $apiKey;
    private $apiSecret;
    
    /**
     * Constructor - Khởi tạo cấu hình Cloudinary từ biến môi trường
     */
    public function __construct() {
        $this->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? 'duhncgkpo';
        $this->apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? '425358843362883';
        $this->apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? 'LWXbOOgeXvXmo2ASjXtpeIr6w1U';
    }
    
    /**
     * Lấy cấu hình Cloudinary
     * 
     * @return array Mảng cấu hình Cloudinary
     */
    public function getConfig() {
        return [
            'cloud_name' => $this->cloudName,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret
        ];
    }
    
    /**
     * Lấy URL Cloudinary API base
     * 
     * @return string URL Cloudinary API base
     */
    public function getApiBaseUrl() {
        return "https://api.cloudinary.com/v1_1/{$this->cloudName}";
    }
    
    /**
     * Lấy cloud name
     * 
     * @return string Cloud name
     */
    public function getCloudName() {
        return $this->cloudName;
    }
    
    /**
     * Lấy API key
     * 
     * @return string API key
     */
    public function getApiKey() {
        return $this->apiKey;
    }
    
    /**
     * Lấy API secret
     * 
     * @return string API secret
     */
    public function getApiSecret() {
        return $this->apiSecret;
    }
    
    /**
     * Tạo signature cho upload
     * 
     * @param array $params Tham số upload
     * @return string Chữ ký (signature)
     */
    public function generateSignature($params) {
        // Sắp xếp các tham số theo thứ tự bảng chữ cái
        ksort($params);
        
        // Tạo chuỗi tham số
        $signatureString = '';
        foreach ($params as $key => $value) {
            if ($key != 'file' && $key != 'resource_type') {
                $signatureString .= $key . '=' . $value . '&';
            }
        }
        
        // Loại bỏ dấu & cuối cùng
        $signatureString = rtrim($signatureString, '&');
        
        // Tạo chữ ký
        return hash('sha256', $signatureString . $this->apiSecret);
    }
} 