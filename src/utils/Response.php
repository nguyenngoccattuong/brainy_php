<?php
namespace App\Utils;

/**
 * Class Response
 * Chuẩn hóa response trả về từ API
 */
class Response {
    /**
     * Trả về response thành công
     * 
     * @param mixed $data Dữ liệu trả về
     * @param string $message Thông báo
     * @param int $code HTTP status code
     * @return array
     */
    public static function success($data = null, $message = 'Thao tác thành công', $code = 200) {
        http_response_code($code);
        return [
            'status' => 'success',
            'code' => $code,
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }
    
    /**
     * Trả về response lỗi
     * 
     * @param string $message Thông báo lỗi
     * @param int $code HTTP status code
     * @param mixed $errors Chi tiết lỗi (nếu có)
     * @return array
     */
    public static function error($message = 'Đã xảy ra lỗi', $code = 400, $errors = null) {
        http_response_code($code);
        $response = [
            'status' => 'error',
            'code' => $code,
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return $response;
    }
    
    /**
     * Trả về response lỗi xác thực
     * 
     * @param string $message Thông báo lỗi
     * @return array
     */
    public static function unauthorized($message = 'Unauthorized') {
        return self::error($message, 401);
    }
    
    /**
     * Trả về response không tìm thấy
     * 
     * @param string $message Thông báo lỗi
     * @return array
     */
    public static function notFound($message = 'Không tìm thấy tài nguyên') {
        return self::error($message, 404);
    }
    
    /**
     * Trả về response lỗi server
     * 
     * @param string $message Thông báo lỗi
     * @return array
     */
    public static function serverError($message = 'Lỗi server') {
        return self::error($message, 500);
    }
    
    /**
     * Trả về response created
     * 
     * @param mixed $data Dữ liệu trả về
     * @param string $message Thông báo
     * @return array
     */
    public static function created($data = null, $message = 'Tạo thành công') {
        return self::success($data, $message, 201);
    }
} 