<?php
namespace App\Routes;

/**
 * Class Router
 * Quản lý và điều hướng các request API
 */
class Router {
    private $routes = [];
    
    public function __construct() {
        $this->registerRoutes();
    }
    
    /**
     * Đăng ký các routes cho API
     */
    private function registerRoutes() {
        // HOME ROUTE
        $this->addRoute('GET', '', 'HomeController', 'index');
        $this->addRoute('GET', '/brainy_php', 'HomeController', 'index');
        
        // AUTH ROUTES
        $this->addRoute('POST', '/api/auth/register', 'AuthController', 'register');
        $this->addRoute('POST', '/api/auth/login', 'AuthController', 'login');
        $this->addRoute('POST', '/api/auth/logout', 'AuthController', 'logout');
        $this->addRoute('POST', '/api/auth/logout-all/([a-f0-9-]+)', 'AuthController', 'logoutAll');
        $this->addRoute('POST', '/api/auth/refresh-token', 'AuthController', 'refreshToken');
        $this->addRoute('POST', '/api/auth/forgot-password', 'AuthController', 'forgotPassword');
        $this->addRoute('GET', '/api/auth/reset-password/([a-zA-Z0-9]+)', 'AuthController', 'validateResetToken');
        $this->addRoute('POST', '/api/auth/reset-password/([a-zA-Z0-9]+)', 'AuthController', 'resetPassword');
        $this->addRoute('POST', '/api/auth/change-password/([a-f0-9-]+)', 'AuthController', 'changePassword');

        // USER ROUTES
        $this->addRoute('GET', '/api/users', 'UserController', 'getAll');
        $this->addRoute('GET', '/api/users/([a-f0-9-]+)', 'UserController', 'getById');
        $this->addRoute('POST', '/api/users', 'UserController', 'create');
        $this->addRoute('PUT', '/api/users/([a-f0-9-]+)', 'UserController', 'update');
        $this->addRoute('DELETE', '/api/users/([a-f0-9-]+)', 'UserController', 'delete');
        
        // USER PROGRESS ROUTES
        $this->addRoute('GET', '/api/users/([a-f0-9-]+)/progress', 'UserController', 'getProgress');
        $this->addRoute('PUT', '/api/users/([a-f0-9-]+)/progress/([a-f0-9-]+)', 'UserController', 'updateProgress');
        $this->addRoute('DELETE', '/api/users/([a-f0-9-]+)/progress/([a-f0-9-]+)', 'UserController', 'deleteProgress');
        
        // USER NOTES ROUTES
        $this->addRoute('GET', '/api/users/([a-f0-9-]+)/notes', 'UserController', 'getNotes');
        $this->addRoute('POST', '/api/users/([a-f0-9-]+)/notes/([a-f0-9-]+)', 'UserController', 'saveNote');
        $this->addRoute('DELETE', '/api/users/([a-f0-9-]+)/notes/([a-f0-9-]+)', 'UserController', 'deleteNote');
        
        // CATEGORY ROUTES
        $this->addRoute('GET', '/api/categories', 'CategoryController', 'getAll');
        $this->addRoute('GET', '/api/categories/([a-f0-9-]+)', 'CategoryController', 'getById');
        $this->addRoute('POST', '/api/categories', 'CategoryController', 'create');
        $this->addRoute('PUT', '/api/categories/([a-f0-9-]+)', 'CategoryController', 'update');
        $this->addRoute('DELETE', '/api/categories/([a-f0-9-]+)', 'CategoryController', 'delete');
        
        // LESSON ROUTES
        $this->addRoute('GET', '/api/lessons', 'LessonController', 'getAll');
        $this->addRoute('GET', '/api/lessons/([a-f0-9-]+)', 'LessonController', 'getById');
        $this->addRoute('GET', '/api/categories/([a-f0-9-]+)/lessons', 'LessonController', 'getByCategoryId');
        $this->addRoute('POST', '/api/lessons', 'LessonController', 'create');
        $this->addRoute('PUT', '/api/lessons/([a-f0-9-]+)', 'LessonController', 'update');
        $this->addRoute('DELETE', '/api/lessons/([a-f0-9-]+)', 'LessonController', 'delete');
        
        // WORD ROUTES
        $this->addRoute('GET', '/api/words', 'WordController', 'getAll');
        $this->addRoute('GET', '/api/words/paginated', 'WordController', 'getAllPaginated');
        $this->addRoute('GET', '/api/words/search', 'WordController', 'search');
        $this->addRoute('GET', '/api/words/random', 'WordController', 'getRandom');
        $this->addRoute('GET', '/api/words/([a-f0-9-]+)', 'WordController', 'getById');
        $this->addRoute('GET', '/api/lessons/([a-f0-9-]+)/words', 'WordController', 'getByLessonId');
        $this->addRoute('POST', '/api/words', 'WordController', 'create');
        $this->addRoute('POST', '/api/words/import', 'WordController', 'import');
        $this->addRoute('POST', '/api/words/import-file', 'WordController', 'importFromFile');
        $this->addRoute('PUT', '/api/words/([a-f0-9-]+)', 'WordController', 'update');
        $this->addRoute('DELETE', '/api/words/([a-f0-9-]+)', 'WordController', 'delete');
        
        // LEARN ROUTES
        $this->addRoute('GET', '/api/learn', 'LearnController', 'getAll');
        $this->addRoute('GET', '/api/learn/status', 'LearnController', 'getByStatus');
        $this->addRoute('GET', '/api/learn/([a-f0-9-]+)', 'LearnController', 'getByWordId');
        $this->addRoute('POST', '/api/learn', 'LearnController', 'create');
        $this->addRoute('PUT', '/api/learn/([a-f0-9-]+)', 'LearnController', 'update');
        $this->addRoute('DELETE', '/api/learn/([a-f0-9-]+)', 'LearnController', 'delete');
        
        // CLOUDINARY ROUTES
        $this->addRoute('POST', '/api/upload', 'CloudinaryController', 'upload');
        $this->addRoute('POST', '/api/upload/lesson-markdown', 'CloudinaryController', 'uploadLessonMarkdown');
        $this->addRoute('GET', '/api/files/([a-f0-9-]+)/([A-Za-z]+)', 'CloudinaryController', 'getByOwner');
        $this->addRoute('DELETE', '/api/files/([a-f0-9-]+)', 'CloudinaryController', 'delete');
    }
    
    /**
     * Thêm một route mới vào danh sách
     * 
     * @param string $method Phương thức HTTP (GET, POST, PUT, DELETE)
     * @param string $path Đường dẫn URL
     * @param string $controller Tên controller sẽ xử lý request
     * @param string $action Phương thức của controller
     */
    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Xử lý request hiện tại
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Loại bỏ query string nếu có
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Loại bỏ trailing slash
        $uri = rtrim($uri, '/');
        
        // Xử lý trường hợp không có mod_rewrite
        // Loại bỏ phần /brainy_php/index.php nếu có
        $uri = preg_replace('#^/brainy_php/index\.php#', '', $uri);
        
        // Xử lý URL gốc
        if ($uri === '/brainy_php' || $uri === '') {
            $this->sendResponse(200, [
                'name' => 'Brainy API',
                'version' => '1.0.0',
                'description' => 'API for vocabulary learning application',
                'endpoints' => $this->getAvailableRoutes()
            ]);
            return;
        }
        
        // Debug info - chỉ trong chế độ dev
        if ($_ENV['DEBUG_MODE'] === 'true') {
            error_log("REQUEST: $method $uri");
        }
        
        // Tìm route phù hợp
        foreach ($this->routes as $route) {
            if ($method !== $route['method']) {
                continue;
            }
            
            // Debug route matcher
            if ($_ENV['DEBUG_MODE'] === 'true') {
                error_log("Checking route: " . $route['path']);
            }
            
            // Kiểm tra xem path có khớp không (hỗ trợ regex)
            $pattern = '@^' . $route['path'] . '$@';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Bỏ phần tử đầu tiên (full match)
                
                // Khởi tạo controller và gọi action
                $controllerClass = 'App\\Controllers\\' . $route['controller'];
                
                if (!class_exists($controllerClass)) {
                    $this->sendResponse(404, ['error' => 'Controller không tồn tại: ' . $controllerClass]);
                    return;
                }
                
                $controller = new $controllerClass();
                $action = $route['action'];
                
                if (!method_exists($controller, $action)) {
                    $this->sendResponse(404, ['error' => 'Phương thức không tồn tại: ' . $action]);
                    return;
                }
                
                try {
                    // Lấy input data từ request
                    $input = $this->getRequestData();
                    
                    // Log request data trong chế độ debug
                    if ($_ENV['DEBUG_MODE'] === 'true') {
                        error_log("Input data: " . json_encode($input));
                    }
                    
                    // Gọi action với params và input data
                    $result = $controller->$action(...array_merge($matches, [$input]));
                    
                    // Trả về kết quả
                    $this->sendResponse(200, $result);
                } catch (\Exception $e) {
                    $this->sendResponse(500, [
                        'error' => $e->getMessage(),
                        'trace' => $_ENV['DEBUG_MODE'] === 'true' ? $e->getTraceAsString() : null
                    ]);
                }
                
                return;
            }
        }
        
        // Không tìm thấy route phù hợp
        $this->sendResponse(404, [
            'error' => 'Không tìm thấy API endpoint: ' . $uri,
            'available_routes' => $_ENV['DEBUG_MODE'] === 'true' ? $this->getAvailableRoutes() : null
        ]);
    }
    
    /**
     * Lấy data từ request
     * 
     * @return array
     */
    private function getRequestData() {
        $data = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = $_GET;
        } else {
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                $data = json_decode($input, true);
            } else {
                $data = $_POST;
            }
        }
        
        return $data;
    }
    
    /**
     * Gửi response về client
     * 
     * @param int $statusCode HTTP status code
     * @param mixed $data Data trả về
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Lấy danh sách các routes có sẵn cho debug
     */
    private function getAvailableRoutes() {
        $routes = [];
        foreach ($this->routes as $route) {
            $routes[] = [
                'method' => $route['method'],
                'path' => $route['path']
            ];
        }
        return $routes;
    }
} 