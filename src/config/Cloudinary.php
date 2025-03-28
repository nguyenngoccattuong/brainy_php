<?php
namespace App\Config;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

/**
 * Class CloudinaryConfig
 * Cấu hình và xử lý kết nối đến Cloudinary để lưu trữ files
 */
class CloudinaryConfig {
    private $cloudinary;
    
    public function __construct() {
        try {
            // Cấu hình Cloudinary từ biến môi trường
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'], 
                    'api_key' => $_ENV['CLOUDINARY_API_KEY'], 
                    'api_secret' => $_ENV['CLOUDINARY_API_SECRET']
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
            
            $this->cloudinary = new Cloudinary();
        } catch (\Exception $e) {
            $logFile = __DIR__ . '/../../logs/cloudinary_error.log';
            $message = date('Y-m-d H:i:s') . ' - Cloudinary Error: ' . $e->getMessage() . "\n";
            file_put_contents($logFile, $message, FILE_APPEND);
            
            die(json_encode([
                'status' => 'error',
                'message' => 'Kết nối Cloudinary thất bại. Vui lòng kiểm tra cấu hình.'
            ]));
        }
    }
    
    /**
     * Lấy instance của Cloudinary
     * @return Cloudinary
     */
    public function getCloudinary() {
        return $this->cloudinary;
    }
    
    /**
     * Upload file lên Cloudinary
     * @param string $filePath Đường dẫn đến file cần upload
     * @param string $folder Thư mục lưu trữ trên Cloudinary
     * @param string $resourceType Loại tài nguyên (image, video, audio, raw)
     * @return array Kết quả upload
     */
    public function uploadFile($filePath, $folder = 'brainy', $resourceType = 'auto') {
        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, [
                'folder' => $folder,
                'resource_type' => $resourceType
            ]);
            
            return $result;
        } catch (\Exception $e) {
            $logFile = __DIR__ . '/../../logs/cloudinary_upload_error.log';
            $message = date('Y-m-d H:i:s') . ' - Upload Error: ' . $e->getMessage() . "\n";
            file_put_contents($logFile, $message, FILE_APPEND);
            
            return [
                'status' => 'error',
                'message' => 'Upload thất bại: ' . $e->getMessage()
            ];
        }
    }
} 