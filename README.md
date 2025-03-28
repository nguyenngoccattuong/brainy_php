# Brainy API

API PHP cho ứng dụng học từ vựng Brainy. Dự án này sử dụng mô hình Model-Controller-Service và kết nối với MySQL thông qua phpMyAdmin.

## Cấu trúc dự án

```
brainy_php/
├── logs/                 # Thư mục chứa log files
├── src/                  # Mã nguồn chính
│   ├── config/           # Cấu hình (database, cloudinary)
│   ├── controllers/      # Controllers xử lý request
│   ├── middleware/       # Middleware (auth, cors, etc.)
│   ├── models/           # Models tương tác với database
│   ├── routes/           # Router điều hướng API
│   ├── services/         # Business logic
│   └── utils/            # Utility functions
├── .env                  # Biến môi trường
├── composer.json         # Quản lý dependency
├── index.php             # Entry point
└── README.md             # File này
```

## Yêu cầu

- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Composer

## Cài đặt

1. Clone repository
```bash
git clone https://github.com/your-username/brainy_php.git
cd brainy_php
```

2. Cài đặt dependencies
```bash
composer install
```

3. Tạo file .env
```bash
cp .env.example .env
```

4. Chỉnh sửa file .env với thông tin của bạn
```bash
DB_HOST=localhost
DB_NAME=brainy
DB_USER=root
DB_PASS=root
DB_PORT=3306

CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

API_URL=http://localhost/brainy_php
DEBUG_MODE=true
```

5. Tạo database
```sql
CREATE DATABASE brainy;
```

6. Import database schema
```bash
mysql -u root -p brainy < database.sql
```

## API Endpoints

### Người dùng

- `GET /api/users` - Lấy danh sách tất cả người dùng (chỉ admin)
- `GET /api/users/{id}` - Lấy thông tin người dùng theo ID
- `POST /api/users` - Tạo người dùng mới
- `PUT /api/users/{id}` - Cập nhật thông tin người dùng
- `DELETE /api/users/{id}` - Xóa người dùng (chỉ admin)

### Xác thực

- `POST /api/auth/login` - Đăng nhập
- `POST /api/auth/register` - Đăng ký tài khoản mới

### Danh mục

- `GET /api/categories` - Lấy danh sách tất cả danh mục
- `GET /api/categories/{id}` - Lấy thông tin danh mục theo ID
- `POST /api/categories` - Tạo danh mục mới
- `PUT /api/categories/{id}` - Cập nhật thông tin danh mục
- `DELETE /api/categories/{id}` - Xóa danh mục

### Bài học

- `GET /api/lessons` - Lấy danh sách tất cả bài học
- `GET /api/lessons/{id}` - Lấy thông tin bài học theo ID
- `GET /api/categories/{id}/lessons` - Lấy danh sách bài học theo danh mục
- `POST /api/lessons` - Tạo bài học mới
- `PUT /api/lessons/{id}` - Cập nhật thông tin bài học
- `DELETE /api/lessons/{id}` - Xóa bài học

### Từ vựng

- `GET /api/words` - Lấy danh sách tất cả từ vựng
- `GET /api/words/{id}` - Lấy thông tin từ vựng theo ID
- `GET /api/lessons/{id}/words` - Lấy danh sách từ vựng theo bài học
- `POST /api/words` - Tạo từ vựng mới
- `PUT /api/words/{id}` - Cập nhật thông tin từ vựng
- `DELETE /api/words/{id}` - Xóa từ vựng

### Tải lên Cloudinary

- `POST /api/upload` - Tải file lên Cloudinary

## Bảo mật

API sử dụng JWT (JSON Web Token) để xác thực. Để truy cập các endpoint được bảo vệ, bạn cần thêm header sau:

```
Authorization: Bearer {token}
```

`{token}` là JWT token nhận được sau khi đăng nhập hoặc đăng ký.

## Phát triển

1. Đảm bảo bạn đã cài đặt Composer
2. Clone repository
3. Cài đặt dependencies: `composer install`
4. Chỉnh sửa file .env với thông tin của bạn
5. Khởi động server: `php -S localhost:8000` 