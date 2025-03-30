# Brainy PHP API

API backend cho ứng dụng học từ vựng Brainy.

## Cài đặt

### Yêu cầu

- PHP >= 7.4
- MySQL/MariaDB
- Composer

### Các bước cài đặt

1. **Clone repository**

```bash
git clone https://github.com/your-username/brainy_php.git
cd brainy_php
```

2. **Cài đặt dependencies**

```bash
composer install
```

3. **Cấu hình môi trường**

```bash
cp .env.example .env
```

Sau đó, chỉnh sửa file `.env` với các thông tin kết nối database và cấu hình khác.

4. **Tạo database**

```bash
# Tạo database 'brainy'
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS brainy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p brainy < database_fixed.sql
mysql -u root -p brainy < auth_tables.sql
```

5. **Chạy server**

Sử dụng MAMP, XAMPP, hoặc PHP built-in server:

```bash
php -S localhost:8000
```

## API Endpoints

Import file `postman_collection.json` và `postman_environment.json` vào Postman để xem và thử nghiệm tất cả các API endpoints.

## Contributing

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request 