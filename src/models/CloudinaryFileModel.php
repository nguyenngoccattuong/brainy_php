<?php
namespace App\Models;

/**
 * Class CloudinaryFileModel
 * Model xử lý dữ liệu bảng cloudinary_files
 */
class CloudinaryFileModel extends Model {
    protected $table = 'cloudinary_files';
    
    /**
     * Tìm file theo public_id
     * 
     * @param string $publicId Public ID của file trên Cloudinary
     * @return array|bool
     */
    public function findByPublicId($publicId) {
        return $this->findOneWhere(['public_id' => $publicId]);
    }
    
    /**
     * Lấy tất cả file của một đối tượng
     * 
     * @param string $ownerId UUID của đối tượng
     * @param string $ownerType Loại đối tượng (User, Word, Category, Lesson)
     * @param string $fileType Loại file (tùy chọn)
     * @return array
     */
    public function getByOwner($ownerId, $ownerType, $fileType = null) {
        $conditions = [
            'owner_id' => $ownerId,
            'owner_type' => $ownerType,
            'status' => 'active'
        ];
        
        if ($fileType) {
            $conditions['file_type'] = $fileType;
        }
        
        return $this->findWhere($conditions);
    }
    
    /**
     * Đánh dấu file đã bị xóa (soft delete)
     * 
     * @param string $id UUID của file
     * @return bool
     */
    public function softDelete($id) {
        return $this->update($id, ['status' => 'deleted']);
    }
    
    /**
     * Lấy file theo loại cho một đối tượng
     * 
     * @param string $ownerId UUID của đối tượng
     * @param string $ownerType Loại đối tượng
     * @param string $fileType Loại file
     * @return array|bool
     */
    public function getFileByType($ownerId, $ownerType, $fileType) {
        $conditions = [
            'owner_id' => $ownerId,
            'owner_type' => $ownerType,
            'file_type' => $fileType,
            'status' => 'active'
        ];
        
        $files = $this->findWhere($conditions);
        
        return !empty($files) ? $files[0] : false;
    }
    
    /**
     * Cập nhật metadata của file
     * 
     * @param string $id UUID của file
     * @param array $metadata Metadata mới
     * @return bool
     */
    public function updateMetadata($id, $metadata) {
        return $this->update($id, [
            'metadata' => json_encode($metadata)
        ]);
    }
    
    /**
     * Tạo file Cloudinary mới
     * 
     * @param array $data Dữ liệu file
     * @return string|bool UUID của file mới hoặc false nếu thất bại
     */
    public function create($data) {
        // Kiểm tra các trường bắt buộc
        $requiredFields = ['owner_id', 'owner_type', 'file_type', 'file_url', 'public_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Thiếu trường bắt buộc: {$field}");
            }
        }
        
        // Kiểm tra xem public_id đã tồn tại chưa
        $existingFile = $this->findByPublicId($data['public_id']);
        if ($existingFile) {
            throw new \Exception('Public ID đã tồn tại');
        }
        
        // Trạng thái mặc định là active nếu không được cung cấp
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'active';
        }
        
        return parent::create($data);
    }
} 