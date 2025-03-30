<?php
namespace App\Controllers;

use App\Config\Database;
use App\Config\Cloudinary;
use App\Models\CloudinaryFileModel;
use App\Middleware\AuthMiddleware;

/**
 * Class CloudinaryController
 * Controller xử lý upload và quản lý file trên Cloudinary
 */
class CloudinaryController {
    private $cloudinaryConfig;
    private $cloudinaryFileModel;
    private $authMiddleware;
    
    /**
     * Constructor
     */
    public function __construct() {
        $db = new Database();
        $this->cloudinaryConfig = new Cloudinary();
        $this->cloudinaryFileModel = new CloudinaryFileModel($db->connect());
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
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra dữ liệu đầu vào
            if (!isset($data['file']) || empty($data['file'])) {
                http_response_code(400);
                return ['error' => 'Thiếu file upload'];
            }
            
            if (!isset($data['owner_id']) || empty($data['owner_id'])) {
                http_response_code(400);
                return ['error' => 'Thiếu owner_id'];
            }
            
            if (!isset($data['owner_type']) || empty($data['owner_type'])) {
                http_response_code(400);
                return ['error' => 'Thiếu owner_type'];
            }
            
            // Loại file được chấp nhận
            $allowedOwnerTypes = ['User', 'Word', 'Category', 'Lesson'];
            if (!in_array($data['owner_type'], $allowedOwnerTypes)) {
                http_response_code(400);
                return ['error' => 'owner_type không hợp lệ. Chấp nhận: ' . implode(', ', $allowedOwnerTypes)];
            }
            
            // Xác định loại file
            $fileType = $this->getFileType($data['file']);
            
            // Tải lên Cloudinary
            $uploadResult = $this->uploadToCloudinary($data['file'], $data['owner_id'], $data['owner_type']);
            
            if (!$uploadResult || isset($uploadResult['error'])) {
                http_response_code(500);
                return ['error' => 'Không thể upload file lên Cloudinary', 'details' => $uploadResult['error'] ?? ''];
            }
            
            // Lưu thông tin file vào database
            $fileData = [
                'owner_id' => $data['owner_id'],
                'owner_type' => $data['owner_type'],
                'file_type' => $fileType,
                'file_url' => $uploadResult['secure_url'],
                'public_id' => $uploadResult['public_id'],
                'format' => $uploadResult['format'],
                'metadata' => json_encode($uploadResult),
                'status' => 'active'
            ];
            
            $fileId = $this->cloudinaryFileModel->create($fileData);
            
            if (!$fileId) {
                http_response_code(500);
                return ['error' => 'Không thể lưu thông tin file'];
            }
            
            $file = $this->cloudinaryFileModel->getById($fileId);
            
            http_response_code(201);
            return [
                'message' => 'Upload file thành công',
                'file' => $file,
                'cloudinary_response' => $uploadResult
            ];
        } catch (\Exception $e) {
            error_log("Upload Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể upload file: ' . $e->getMessage()];
        }
    }
    
    /**
     * Lấy danh sách file theo owner
     * 
     * @param string $ownerId UUID của owner
     * @param string $ownerType Loại owner
     * @return array
     */
    public function getByOwner($ownerId, $ownerType) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            $files = $this->cloudinaryFileModel->getByOwner($ownerId, $ownerType);
            
            return ['files' => $files];
        } catch (\Exception $e) {
            error_log("GetByOwner Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể lấy danh sách file'];
        }
    }
    
    /**
     * Xóa file
     * 
     * @param string $fileId UUID của file
     * @return array
     */
    public function delete($fileId) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra file tồn tại
            $file = $this->cloudinaryFileModel->getById($fileId);
            if (!$file) {
                http_response_code(404);
                return ['error' => 'Không tìm thấy file'];
            }
            
            // Xóa file trên Cloudinary
            $deleteResult = $this->deleteFromCloudinary($file['public_id']);
            
            if (!$deleteResult || isset($deleteResult['error'])) {
                http_response_code(500);
                return ['error' => 'Không thể xóa file trên Cloudinary', 'details' => $deleteResult['error'] ?? ''];
            }
            
            // Xóa thông tin file trong database
            $deleted = $this->cloudinaryFileModel->delete($fileId);
            
            if (!$deleted) {
                http_response_code(500);
                return ['error' => 'Không thể xóa thông tin file'];
            }
            
            return [
                'message' => 'Xóa file thành công',
                'cloudinary_response' => $deleteResult
            ];
        } catch (\Exception $e) {
            error_log("Delete File Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể xóa file: ' . $e->getMessage()];
        }
    }
    
    /**
     * Upload bài học markdown lên Cloudinary
     * 
     * @param array $data Dữ liệu upload
     * @return array
     */
    public function uploadLessonMarkdown($data) {
        // Xác thực người dùng
        $auth = $this->authMiddleware->authenticate();
        if (!$auth) {
            http_response_code(401);
            return ['error' => 'Unauthorized'];
        }
        
        try {
            // Kiểm tra dữ liệu đầu vào
            if (!isset($data['content']) || empty($data['content'])) {
                http_response_code(400);
                return ['error' => 'Thiếu nội dung markdown'];
            }
            
            if (!isset($data['lesson_id']) || empty($data['lesson_id'])) {
                http_response_code(400);
                return ['error' => 'Thiếu lesson_id'];
            }
            
            // Tạo file markdown tạm thời
            $tempFile = tempnam(sys_get_temp_dir(), 'markdown_');
            file_put_contents($tempFile, $data['content']);
            
            // Upload lên Cloudinary
            $uploadResult = $this->uploadToCloudinary(
                $tempFile, 
                $data['lesson_id'], 
                'Lesson', 
                ['resource_type' => 'raw', 'format' => 'md']
            );
            
            // Xóa file tạm
            unlink($tempFile);
            
            if (!$uploadResult || isset($uploadResult['error'])) {
                http_response_code(500);
                return ['error' => 'Không thể upload file markdown lên Cloudinary', 'details' => $uploadResult['error'] ?? ''];
            }
            
            // Lưu thông tin file vào database
            $fileData = [
                'owner_id' => $data['lesson_id'],
                'owner_type' => 'Lesson',
                'file_type' => 'document',
                'file_url' => $uploadResult['secure_url'],
                'public_id' => $uploadResult['public_id'],
                'format' => 'md',
                'metadata' => json_encode($uploadResult),
                'status' => 'active'
            ];
            
            $fileId = $this->cloudinaryFileModel->create($fileData);
            
            if (!$fileId) {
                http_response_code(500);
                return ['error' => 'Không thể lưu thông tin file markdown'];
            }
            
            $file = $this->cloudinaryFileModel->getById($fileId);
            
            http_response_code(201);
            return [
                'message' => 'Upload file markdown thành công',
                'file' => $file,
                'cloudinary_response' => $uploadResult
            ];
        } catch (\Exception $e) {
            error_log("Upload Markdown Error: " . $e->getMessage());
            http_response_code(500);
            return ['error' => 'Không thể upload file markdown: ' . $e->getMessage()];
        }
    }
    
    /**
     * Xác định loại file
     * 
     * @param string $file Đường dẫn file hoặc base64
     * @return string Loại file (image, audio, video, document)
     */
    private function getFileType($file) {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            // URL file
            $extension = pathinfo(parse_url($file, PHP_URL_PATH), PATHINFO_EXTENSION);
        } else if (file_exists($file)) {
            // Local file
            $extension = pathinfo($file, PATHINFO_EXTENSION);
        } else {
            // Base64
            $matches = [];
            preg_match('/^data:([^;]+);base64,/', $file, $matches);
            $mime = isset($matches[1]) ? $matches[1] : 'application/octet-stream';
            
            if (strpos($mime, 'image/') === 0) {
                return 'image';
            } else if (strpos($mime, 'audio/') === 0) {
                return 'audio';
            } else if (strpos($mime, 'video/') === 0) {
                return 'video';
            } else {
                return 'document';
            }
        }
        
        // Xác định loại file từ extension
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'aac'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
        
        $extension = strtolower($extension);
        
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } else if (in_array($extension, $audioExtensions)) {
            return 'audio';
        } else if (in_array($extension, $videoExtensions)) {
            return 'video';
        } else {
            return 'document';
        }
    }
    
    /**
     * Upload file lên Cloudinary
     * 
     * @param string $file Đường dẫn file hoặc base64
     * @param string $ownerId UUID của owner
     * @param string $ownerType Loại owner
     * @param array $options Tùy chọn upload
     * @return array Kết quả upload
     */
    private function uploadToCloudinary($file, $ownerId, $ownerType, $options = []) {
        // Cấu hình Cloudinary
        $config = $this->cloudinaryConfig->getConfig();
        $apiKey = $config['api_key'];
        $apiSecret = $config['api_secret'];
        $cloudName = $config['cloud_name'];
        
        // Folder trên Cloudinary
        $folder = "brainy/{$ownerType}/{$ownerId}";
        
        // Timestamp
        $timestamp = time();
        
        // Tùy chọn upload mặc định
        $defaultOptions = [
            'resource_type' => 'auto',
            'folder' => $folder,
            'timestamp' => $timestamp
        ];
        
        // Merge với tùy chọn người dùng
        $uploadOptions = array_merge($defaultOptions, $options);
        
        // Tạo signature
        $signature = $this->cloudinaryConfig->generateSignature($uploadOptions);
        
        // Chuẩn bị dữ liệu upload
        $uploadData = [
            'file' => $this->prepareFile($file),
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder
        ];
        
        // Thêm các tùy chọn khác
        foreach ($options as $key => $value) {
            if ($key != 'folder' && $key != 'timestamp') {
                $uploadData[$key] = $value;
            }
        }
        
        // URL upload
        $resourceType = $options['resource_type'] ?? 'auto';
        $uploadUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/{$resourceType}/upload";
        
        // Upload file
        $result = $this->curlUpload($uploadUrl, $uploadData);
        
        return $result;
    }
    
    /**
     * Chuẩn bị file để upload
     * 
     * @param string $file Đường dẫn file hoặc base64
     * @return string File để upload
     */
    private function prepareFile($file) {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            // URL file
            return $file;
        } else if (file_exists($file)) {
            // Local file
            return new \CURLFile($file);
        } else {
            // Base64
            return $file;
        }
    }
    
    /**
     * Upload file qua CURL
     * 
     * @param string $url URL upload
     * @param array $data Dữ liệu upload
     * @return array Kết quả upload
     */
    private function curlUpload($url, $data) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            return ['error' => $error];
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Xóa file trên Cloudinary
     * 
     * @param string $publicId Public ID của file
     * @return array Kết quả xóa
     */
    private function deleteFromCloudinary($publicId) {
        // Cấu hình Cloudinary
        $config = $this->cloudinaryConfig->getConfig();
        $apiKey = $config['api_key'];
        $apiSecret = $config['api_secret'];
        $cloudName = $config['cloud_name'];
        
        // Timestamp
        $timestamp = time();
        
        // Tạo signature
        $signature = $this->cloudinaryConfig->generateSignature([
            'public_id' => $publicId,
            'timestamp' => $timestamp
        ]);
        
        // Chuẩn bị dữ liệu xóa
        $deleteData = [
            'public_id' => $publicId,
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature
        ];
        
        // URL xóa
        $deleteUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";
        
        // Xóa file
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $deleteUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $deleteData
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            return ['error' => $error];
        }
        
        return json_decode($response, true);
    }
} 