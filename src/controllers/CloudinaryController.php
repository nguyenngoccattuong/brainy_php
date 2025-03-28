<?php
namespace App\Controllers;

use App\Config\Database;
use App\Config\CloudinaryConfig;
use App\Middleware\AuthMiddleware;
use App\Models\CloudinaryFileModel;

/**
 * Class CloudinaryController
 * Controller xử lý tải file lên Cloudinary
 */
class CloudinaryController {
    private $cloudinaryConfig;
    private $cloudinaryFileModel;
    private $authMiddleware;
    
    public function __construct() {
        $db = new Database();
        $conn = $db->connect();
        
        $this->cloudinaryConfig = new CloudinaryConfig();
        $this->cloudinaryFileModel = new CloudinaryFileModel($conn);
        $this->authMiddleware = new AuthMiddleware();
    }
    
    /**
     * Upload file lên Cloudinary
     * 
     * @param array $data Dữ liệu upload
     * @return array
     */
    public function upload($data) {
        // Xác thực người dùng
        $currentUser = $this->authMiddleware->authenticate();
        if (!$currentUser) {
            http_response_code(401);
            return ['error' => 'Không có quyền truy cập'];
        }
        
        // Kiểm tra file đã được upload lên server
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            return ['error' => 'Không tìm thấy file hợp lệ'];
        }
        
        // Kiểm tra các tham số bắt buộc
        if (!isset($data['owner_type']) || empty($data['owner_type'])) {
            http_response_code(400);
            return ['error' => 'Thiếu thông tin owner_type'];
        }
        
        // Nếu owner_id không được cung cấp, sử dụng ID của người dùng hiện tại
        $ownerId = $data['owner_id'] ?? $currentUser['id'];
        
        // Xác định loại file
        $fileType = $this->getFileType($_FILES['file']['type']);
        
        if (!$fileType) {
            http_response_code(400);
            return ['error' => 'Loại file không được hỗ trợ'];
        }
        
        try {
            // Tạo thư mục theo loại tài nguyên
            $folder = 'brainy/' . strtolower($data['owner_type']) . 's';
            
            // Tải file lên Cloudinary
            $uploadResult = $this->cloudinaryConfig->uploadFile(
                $_FILES['file']['tmp_name'],
                $folder,
                $fileType === 'audio' ? 'video' : $fileType
            );
            
            if (!isset($uploadResult['public_id'])) {
                http_response_code(500);
                return ['error' => 'Tải file lên Cloudinary thất bại', 'details' => $uploadResult];
            }
            
            // Lưu thông tin file vào database
            $fileData = [
                'owner_id' => $ownerId,
                'owner_type' => $data['owner_type'],
                'file_type' => $fileType,
                'file_url' => $uploadResult['secure_url'],
                'public_id' => $uploadResult['public_id'],
                'format' => $uploadResult['format'] ?? null,
                'metadata' => json_encode($uploadResult),
                'status' => 'active'
            ];
            
            $fileId = $this->cloudinaryFileModel->create($fileData);
            
            if (!$fileId) {
                http_response_code(500);
                return ['error' => 'Không thể lưu thông tin file vào database'];
            }
            
            // Lấy thông tin file đã lưu
            $fileInfo = $this->cloudinaryFileModel->getById($fileId);
            
            return [
                'success' => true,
                'message' => 'Upload file thành công',
                'file' => $fileInfo
            ];
        } catch (\Exception $e) {
            http_response_code(500);
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Xác định loại file từ MIME type
     * 
     * @param string $mimeType MIME type của file
     * @return string|null Loại file (image, audio, video, document) hoặc null nếu không hỗ trợ
     */
    private function getFileType($mimeType) {
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } else if (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        } else if (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } else if (in_array($mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ])) {
            return 'document';
        }
        
        return null;
    }
} 