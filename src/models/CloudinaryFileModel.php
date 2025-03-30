<?php
namespace App\Models;

class CloudinaryFileModel extends Model {
    protected $table = 'cloudinary_files';
    
    /**
     * Lấy danh sách tất cả files
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy file theo ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Lấy files theo owner_id và owner_type
     */
    public function getByOwner($ownerId, $ownerType) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE owner_id = :owner_id AND owner_type = :owner_type 
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':owner_id', $ownerId);
        $stmt->bindValue(':owner_type', $ownerType);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo file mới
     */
    public function create($data) {
        return parent::create($data);
    }
    
    /**
     * Cập nhật file
     */
    public function update($id, $data) {
        return parent::update($id, $data);
    }
    
    /**
     * Xóa file
     */
    public function delete($id) {
        return parent::delete($id);
    }
    
    /**
     * Xóa files theo owner_id và owner_type
     */
    public function deleteByOwner($ownerId, $ownerType) {
        $sql = "DELETE FROM {$this->table} 
                WHERE owner_id = :owner_id AND owner_type = :owner_type";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':owner_id', $ownerId);
        $stmt->bindValue(':owner_type', $ownerType);
        return $stmt->execute();
    }
} 