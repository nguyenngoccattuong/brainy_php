<?php
/**
 * Brainy API - Điểm khởi đầu ứng dụng
 * 
 * File này nhận tất cả các request và điều hướng đến router
 */

// Hiển thị tất cả lỗi trong chế độ dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load các thư viện và file cấu hình
require_once 'vendor/autoload.php';
require_once 'src/config/Database.php';
require_once 'src/routes/Router.php';

// Load biến môi trường từ file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Debug logging for environment variables
if ($_ENV['DEBUG_MODE'] === 'true') {
    error_log("Environment variables loaded:");
    error_log("DB_HOST: " . $_ENV['DB_HOST']);
    error_log("DB_NAME: " . $_ENV['DB_NAME']);
    error_log("JWT_SECRET: " . ($_ENV['JWT_SECRET'] ?? 'not set'));
    error_log("DEBUG_MODE: " . $_ENV['DEBUG_MODE']);
}

// Thiết lập debug log
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

if ($_ENV['DEBUG_MODE'] === 'true') {
    ini_set('log_errors', 1);
    ini_set('error_log', $logDir . '/error.log');
    error_log('Request received: ' . $_SERVER['REQUEST_URI']);
}

// Cho phép CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Xử lý routing
$router = new \App\Routes\Router();
$router->handleRequest();