# Brainy API

Brainy API là hệ thống API dành cho ứng dụng học từ vựng, cho phép quản lý người dùng, danh mục, bài học, từ vựng và tiến độ học tập.

## Cài đặt

### Yêu cầu

- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Composer
- MAMP/XAMPP/WAMP (hoặc môi trường web server khác)

### Hướng dẫn cài đặt

1. Clone dự án:
```
git clone <repository-url> brainy_php
cd brainy_php
```

2. Cài đặt các dependency bằng Composer:
```
composer install
```

3. Tạo file .env:
```
cp .env
```

4. Cấu hình trong file .env:
```
DB_HOST=localhost
DB_NAME=brainy
DB_USER=root
DB_PASS=root
DB_PORT=3306

JWT_SECRET=your_jwt_secret_key
JWT_EXPIRATION=3600 # seconds

CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

DEBUG_MODE=false
```

5. Tạo database và import schema:
```
mysql -u root -p < database_fixed.sql
```

6. Cấu hình web server để trỏ đến thư mục gốc của dự án.

## Cấu trúc thư mục

```
├── docs/                 # Tài liệu API và hướng dẫn
├── logs/                 # Log files
├── src/
│   ├── config/           # Cấu hình hệ thống
│   ├── controllers/      # Controllers xử lý request
│   ├── middleware/       # Middleware (xác thực, etc.)
│   ├── models/           # Models tương tác với database
│   ├── routes/           # Router URL
│   ├── services/         # Business logic
│   └── utils/            # Các tiện ích
├── vendor/               # Thư viện (được cài đặt qua Composer)
├── .env                  # Biến môi trường
├── .gitignore
├── composer.json
├── database_fixed.sql    # Schema database
├── index.php             # Entry point
└── README.md
```

## Sử dụng API

API documentation có sẵn trong file [api_documentation.md](api_documentation.md).

### Postman Collection

Bạn có thể import file `brainy_api_collection.json` vào Postman để dễ dàng test các endpoint API.

### Authentication

API sử dụng JWT (JSON Web Token) để xác thực. Để sử dụng API, bạn cần:

1. Đăng ký tài khoản bằng endpoint `/api/auth/register`
2. Đăng nhập để lấy token bằng endpoint `/api/auth/login`
3. Sử dụng token nhận được trong header `Authorization: Bearer {token}` khi gọi các API khác

## Các tính năng chính

- Quản lý người dùng (đăng ký, đăng nhập, quản lý thông tin)
- Quản lý danh mục và bài học
- Quản lý từ vựng (thêm, sửa, xóa, tìm kiếm, import từ JSON)
- Theo dõi tiến độ học tập của người dùng
- Lưu trữ ghi chú của người dùng

## Import từ vựng

Hệ thống hỗ trợ import từ vựng với cấu trúc JSON như sau:

```json
[
  {
    "word": "a",
    "pos": "indefinite article",
    "phonetic": "https://www.oxfordlearnersdictionaries.com/media/english/uk_pron/a/a__/a__gb/a__gb_2.mp3",
    "phonetic_text": "/ə/",
    "phonetic_am": "https://www.oxfordlearnersdictionaries.com/media/english/us_pron/a/a__/a__us/a__us_2_rr.mp3",
    "phonetic_am_text": "/ə/",
    "senses": [
      {
        "definition": "used before countable or singular nouns referring to people or things that have not already been mentioned",
        "examples": [
          {
            "cf": "",
            "x": "a man/horse/unit"
          },
          {
            "cf": "",
            "x": "an aunt/egg/hour/X-ray"
          }
        ]
      }
    ]
  }
]
``` 