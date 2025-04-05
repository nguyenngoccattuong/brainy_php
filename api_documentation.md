# Brainy API Documentation

## Overview

Brainy API là hệ thống API dành cho ứng dụng học từ vựng. API này cho phép quản lý người dùng, danh mục, bài học, từ vựng và tiến độ học tập.

## Base URL

```
http://localhost/brainy_php/index.php
```

## Authentication

API sử dụng JWT (JSON Web Token) để xác thực người dùng. Mọi request đến API (trừ đăng ký và đăng nhập) đều phải kèm theo token trong header:

```
Authorization: Bearer {access_token}
```

### Authentication Endpoints

#### Register

```
POST /api/auth/register
```

**Request Body:**
```json
{
    "username": "testuser",
    "email": "test@example.com",
    "password": "password123",
    "full_name": "Test User"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 201,
    "success": true,
    "message": "Đăng ký thành công",
    "data": {
        "user": {
            "id": "uuid-string",
            "username": "testuser",
            "email": "test@example.com",
            "full_name": "Test User",
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00"
        },
        "token": {
            "access_token": "jwt-token-string",
            "refresh_token": "refresh-token-string",
            "expires_in": 3600
        }
    }
}
```

#### Login

```
POST /api/auth/login
```

**Request Body:**
```json
{
    "username": "testuser",
    "password": "password123"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Đăng nhập thành công",
    "data": {
        "user": {
            "id": "uuid-string",
            "username": "testuser",
            "email": "test@example.com",
            "full_name": "Test User"
        },
        "token": {
            "access_token": "jwt-token-string",
            "refresh_token": "refresh-token-string",
            "expires_in": 3600
        }
    }
}
```

#### Logout

```
POST /api/auth/logout
```

**Request Body:**
```json
{
    "refresh_token": "refresh-token-string"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Đăng xuất thành công",
    "data": null
}
```

#### Refresh Token

```
POST /api/auth/refresh-token
```

**Request Body:**
```json
{
    "refresh_token": "refresh-token-string"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Refresh token thành công",
    "data": {
        "access_token": "new-jwt-token-string",
        "refresh_token": "new-refresh-token-string",
        "expires_in": 3600
    }
}
```

## User Endpoints

### Get All Users

```
GET /api/users
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Lấy danh sách users thành công",
    "data": {
        "users": [
            {
                "id": "uuid-string",
                "username": "testuser",
                "email": "test@example.com",
                "full_name": "Test User",
                "created_at": "2023-06-15 10:00:00",
                "updated_at": "2023-06-15 10:00:00"
            }
        ]
    }
}
```

### Get User by ID

```
GET /api/users/{user_id}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Lấy thông tin user thành công",
    "data": {
        "user": {
            "id": "uuid-string",
            "username": "testuser",
            "email": "test@example.com",
            "full_name": "Test User",
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00"
        }
    }
}
```

### Create User

```
POST /api/users
```

**Request Body:**
```json
{
    "username": "newuser",
    "email": "newuser@example.com",
    "password": "password123",
    "full_name": "New User"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 201,
    "success": true,
    "message": "Tạo user thành công",
    "data": {
        "user": {
            "id": "uuid-string",
            "username": "newuser",
            "email": "newuser@example.com",
            "full_name": "New User",
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00"
        }
    }
}
```

### Update User

```
PUT /api/users/{user_id}
```

**Request Body:**
```json
{
    "username": "updateuser",
    "email": "update@example.com",
    "full_name": "Updated User"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Cập nhật thông tin thành công",
    "data": {
        "user": {
            "id": "uuid-string",
            "username": "updateuser",
            "email": "update@example.com",
            "full_name": "Updated User",
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00"
        }
    }
}
```

### Delete User

```
DELETE /api/users/{user_id}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Xóa user thành công",
    "data": null
}
```

### Get User Progress

```
GET /api/users/{user_id}/progress
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Lấy tiến độ học thành công",
    "data": {
        "progress": [
            {
                "id": "uuid-string",
                "user_id": "user-uuid-string",
                "word_id": "word-uuid-string",
                "status": "learning",
                "last_review": "2023-06-15 10:00:00",
                "next_review": "2023-06-16 10:00:00",
                "review_count": 1,
                "word": "abandon",
                "phonetic": "https://www.example.com/audio.mp3",
                "phonetic_text": "/əˈbændən/"
            }
        ]
    }
}
```

### Update User Progress

```
PUT /api/users/{user_id}/progress/{word_id}
```

**Request Body:**
```json
{
    "status": "learning",
    "last_review": "2023-06-15 10:00:00",
    "next_review": "2023-06-16 10:00:00",
    "review_count": 1
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Cập nhật tiến độ thành công",
    "data": {
        "progress": true
    }
}
```

### Delete User Progress

```
DELETE /api/users/{user_id}/progress/{word_id}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Xóa tiến độ học thành công",
    "data": null
}
```

### Get User Notes

```
GET /api/users/{user_id}/notes
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Lấy ghi chú thành công",
    "data": {
        "notes": [
            {
                "id": "uuid-string",
                "user_id": "user-uuid-string",
                "word_id": "word-uuid-string",
                "note": "This is my note for this word",
                "created_at": "2023-06-15 10:00:00",
                "updated_at": "2023-06-15 10:00:00",
                "word": "abandon",
                "phonetic": "https://www.example.com/audio.mp3",
                "phonetic_text": "/əˈbændən/"
            }
        ]
    }
}
```

### Save User Note

```
POST /api/users/{user_id}/notes/{word_id}
```

**Request Body:**
```json
{
    "note": "This is my note for this word"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Lưu ghi chú thành công",
    "data": {
        "note": true
    }
}
```

### Delete User Note

```
DELETE /api/users/{user_id}/notes/{word_id}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Xóa ghi chú thành công",
    "data": null
}
```

## Word Endpoints

### Get All Words

```
GET /api/words
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Thao tác thành công",
    "data": {
        "words": [
            {
                "id": "uuid-string",
                "lesson_id": null,
                "word": "assistance",
                "pos": "noun",
                "phonetic": "https://www.oxfordlearnersdictionaries.com/media/english/uk_pron/a/ass/assis/assistance__gb_2.mp3",
                "phonetic_text": "/əˈsɪstəns/",
                "phonetic_am": "https://www.oxfordlearnersdictionaries.com/media/english/us_pron/a/ass/assis/assistance__us_1.mp3",
                "phonetic_am_text": "/əˈsɪstəns/",
                "audio_id": null,
                "image_id": null,
                "created_at": "2023-06-15 10:00:00",
                "updated_at": "2023-06-15 10:00:00",
                "audio_url": null,
                "image_url": null,
                "lesson_title": null,
                "senses": [
                    {
                        "id": "sense-uuid-string",
                        "word_id": "uuid-string",
                        "definition": "help or support given to someone",
                        "created_at": "2023-06-15 10:00:00",
                        "updated_at": "2023-06-15 10:00:00",
                        "examples": [
                            {
                                "id": "example-uuid-string",
                                "sense_id": "sense-uuid-string",
                                "cf": "",
                                "x": "Thank you for your assistance.",
                                "created_at": "2023-06-15 10:00:00",
                                "updated_at": "2023-06-15 10:00:00"
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```

### Get Word by ID

```
GET /api/words/{word_id}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Thao tác thành công",
    "data": {
        "word": {
            "id": "uuid-string",
            "lesson_id": null,
            "word": "assistance",
            "pos": "noun",
            "phonetic": "https://www.oxfordlearnersdictionaries.com/media/english/uk_pron/a/ass/assis/assistance__gb_2.mp3",
            "phonetic_text": "/əˈsɪstəns/",
            "phonetic_am": "https://www.oxfordlearnersdictionaries.com/media/english/us_pron/a/ass/assis/assistance__us_1.mp3",
            "phonetic_am_text": "/əˈsɪstəns/",
            "audio_id": null,
            "image_id": null,
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00",
            "audio_url": null,
            "image_url": null,
            "lesson_title": null,
            "senses": [
                {
                    "id": "sense-uuid-string",
                    "word_id": "uuid-string",
                    "definition": "help or support given to someone",
                    "created_at": "2023-06-15 10:00:00",
                    "updated_at": "2023-06-15 10:00:00",
                    "examples": [
                        {
                            "id": "example-uuid-string",
                            "sense_id": "sense-uuid-string",
                            "cf": "",
                            "x": "Thank you for your assistance.",
                            "created_at": "2023-06-15 10:00:00",
                            "updated_at": "2023-06-15 10:00:00"
                        }
                    ]
                }
            ]
        }
    }
}
```

### Search Words

```
GET /api/words/search?keyword=example
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Thao tác thành công",
    "data": {
        "words": [
            {
                "id": "uuid-string",
                "lesson_id": null,
                "word": "example",
                "pos": "noun",
                "phonetic": "https://www.example.com/audio.mp3",
                "phonetic_text": "/ɪɡˈzɑːmpl/",
                "phonetic_am": "https://www.example.com/audio_am.mp3",
                "phonetic_am_text": "/ɪɡˈzæmpl/",
                "audio_id": null,
                "image_id": null,
                "created_at": "2023-06-15 10:00:00",
                "updated_at": "2023-06-15 10:00:00",
                "audio_url": null,
                "image_url": null,
                "lesson_title": null,
                "senses": [
                    {
                        "id": "sense-uuid-string",
                        "word_id": "uuid-string",
                        "definition": "a thing characteristic of its kind",
                        "created_at": "2023-06-15 10:00:00",
                        "updated_at": "2023-06-15 10:00:00",
                        "examples": [
                            {
                                "id": "example-uuid-string",
                                "sense_id": "sense-uuid-string",
                                "cf": "",
                                "x": "This is an example of modern architecture.",
                                "created_at": "2023-06-15 10:00:00",
                                "updated_at": "2023-06-15 10:00:00"
                            }
                        ]
                    }
                ]
            }
        ]
    }
}
```

### Create Word

```
POST /api/words
```

**Request Body:**
```json
{
    "word": "example",
    "pos": "noun",
    "phonetic": "https://www.example.com/audio.mp3",
    "phonetic_text": "/ɪɡˈzɑːmpl/",
    "phonetic_am": "https://www.example.com/audio_am.mp3",
    "phonetic_am_text": "/ɪɡˈzæmpl/",
    "senses": [
        {
            "definition": "a thing characteristic of its kind",
            "examples": [
                {
                    "cf": "",
                    "x": "This is an example of modern architecture."
                }
            ]
        }
    ]
}
```

**Response:**
```json
{
    "status": "success",
    "code": 201,
    "success": true,
    "message": "Tạo word thành công",
    "data": {
        "word": {
            "id": "uuid-string",
            "lesson_id": null,
            "word": "example",
            "pos": "noun",
            "phonetic": "https://www.example.com/audio.mp3",
            "phonetic_text": "/ɪɡˈzɑːmpl/",
            "phonetic_am": "https://www.example.com/audio_am.mp3",
            "phonetic_am_text": "/ɪɡˈzæmpl/",
            "audio_id": null,
            "image_id": null,
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00",
            "audio_url": null,
            "image_url": null,
            "lesson_title": null,
            "senses": [
                {
                    "id": "sense-uuid-string",
                    "word_id": "uuid-string",
                    "definition": "a thing characteristic of its kind",
                    "created_at": "2023-06-15 10:00:00",
                    "updated_at": "2023-06-15 10:00:00",
                    "examples": [
                        {
                            "id": "example-uuid-string",
                            "sense_id": "sense-uuid-string",
                            "cf": "",
                            "x": "This is an example of modern architecture.",
                            "created_at": "2023-06-15 10:00:00",
                            "updated_at": "2023-06-15 10:00:00"
                        }
                    ]
                }
            ]
        }
    }
}
```

### Update Word

```
PUT /api/words/{word_id}
```

**Request Body:**
```json
{
    "word": "updated_example",
    "pos": "noun",
    "phonetic_text": "/ʌpˈdeɪtɪd ɪɡˈzɑːmpl/"
}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Cập nhật thông tin thành công",
    "data": {
        "word": {
            "id": "uuid-string",
            "lesson_id": null,
            "word": "updated_example",
            "pos": "noun",
            "phonetic": "https://www.example.com/audio.mp3",
            "phonetic_text": "/ʌpˈdeɪtɪd ɪɡˈzɑːmpl/",
            "phonetic_am": "https://www.example.com/audio_am.mp3",
            "phonetic_am_text": "/ɪɡˈzæmpl/",
            "audio_id": null,
            "image_id": null,
            "created_at": "2023-06-15 10:00:00",
            "updated_at": "2023-06-15 10:00:00",
            "audio_url": null,
            "image_url": null,
            "lesson_title": null
        }
    }
}
```

### Delete Word

```
DELETE /api/words/{word_id}
```

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Xóa word thành công",
    "data": null
}
```

### Import Words

```
POST /api/words/import
```

**Request Body:**
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

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Import thành công",
    "data": {
        "result": {
            "success": true,
            "imported": 1,
            "errors": [],
            "words": [
                {
                    "id": "uuid-string",
                    "lesson_id": null,
                    "word": "a",
                    "pos": "indefinite article",
                    "phonetic": "https://www.oxfordlearnersdictionaries.com/media/english/uk_pron/a/a__/a__gb/a__gb_2.mp3",
                    "phonetic_text": "/ə/",
                    "phonetic_am": "https://www.oxfordlearnersdictionaries.com/media/english/us_pron/a/a__/a__us/a__us_2_rr.mp3",
                    "phonetic_am_text": "/ə/",
                    "audio_id": null,
                    "image_id": null,
                    "created_at": "2023-06-15 10:00:00",
                    "updated_at": "2023-06-15 10:00:00",
                    "audio_url": null,
                    "image_url": null,
                    "lesson_title": null,
                    "senses": [
                        {
                            "id": "sense-uuid-string",
                            "word_id": "uuid-string",
                            "definition": "used before countable or singular nouns referring to people or things that have not already been mentioned",
                            "created_at": "2023-06-15 10:00:00",
                            "updated_at": "2023-06-15 10:00:00",
                            "examples": [
                                {
                                    "id": "example-uuid-string",
                                    "sense_id": "sense-uuid-string",
                                    "cf": "",
                                    "x": "a man/horse/unit",
                                    "created_at": "2023-06-15 10:00:00",
                                    "updated_at": "2023-06-15 10:00:00"
                                },
                                {
                                    "id": "example-uuid-string",
                                    "sense_id": "sense-uuid-string",
                                    "cf": "",
                                    "x": "an aunt/egg/hour/X-ray",
                                    "created_at": "2023-06-15 10:00:00",
                                    "updated_at": "2023-06-15 10:00:00"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    }
}
```

### Import Words From File

```
POST /api/words/import-file
```

**Request Body:**
- Form-data với key `file` và value là file JSON chứa danh sách các từ vựng

**Response:**
```json
{
    "status": "success",
    "code": 200,
    "success": true,
    "message": "Import thành công",
    "data": {
        "result": {
            "success": true,
            "imported": 2,
            "errors": [],
            "words": [
                // Danh sách các từ đã import
            ]
        }
    }
}
```

## Error Responses

### Unauthorized

```json
{
    "status": "error",
    "code": 401,
    "success": false,
    "message": "Unauthorized"
}
```

### Not Found

```json
{
    "status": "error",
    "code": 404,
    "success": false,
    "message": "Không tìm thấy tài nguyên"
}
```

### Bad Request

```json
{
    "status": "error",
    "code": 400,
    "success": false,
    "message": "Username là bắt buộc"
}
```

### Server Error

```json
{
    "status": "error",
    "code": 500,
    "success": false,
    "message": "Lỗi server"
}
```

## Tài liệu API

### Mô tả chung

Api được xây dựng theo chuẩn RESTful, với các endpoint được định nghĩa với HTTP method tương ứng.

- Các endpoint đều trả về dữ liệu dưới dạng JSON.
- Các endpoint cần xác thực sẽ yêu cầu `Authorization` header với giá trị là `Bearer <access_token>`.
- Các endpoint lấy danh sách có hỗ trợ phân trang với các tham số `page` và `limit`.
- Mã lỗi HTTP trả về tuân theo chuẩn.

### Endpoints được hỗ trợ

## Learn API

### Get All Learn Status

Lấy tất cả trạng thái học của người dùng hiện tại

- **URL:** `/api/learn`
- **Method:** `GET`
- **Auth Required:** Yes
- **Permissions Required:** User

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
```
page: Số trang (mặc định: 1)
limit: Số lượng kết quả mỗi trang (mặc định: 10)
```

**Success Response:**
```json
{
  "status": "success",
  "code": 200,
  "success": true,
  "message": "Lấy danh sách trạng thái học thành công",
  "data": {
    "learn": {
      "items": [
        {
          "id": "uuid",
          "user_id": "uuid",
          "word_id": "uuid",
          "status": "learning",
          "created_at": "datetime",
          "updated_at": "datetime",
          "word": "example",
          "pos": "n",
          "phonetic_text": "/ɪɡˈzɑːmpl/",
          "audio_url": "https://res.cloudinary.com/...",
          "image_url": "https://res.cloudinary.com/..."
        }
      ],
      "total": 10,
      "page": 1,
      "limit": 10,
      "total_pages": 1
    }
  }
}
```

### Get Learn Status By Status

Lấy trạng thái học lọc theo status

- **URL:** `/api/learn/status`
- **Method:** `GET`
- **Auth Required:** Yes
- **Permissions Required:** User

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
```
status: Trạng thái học (learning, learned, skip)
page: Số trang (mặc định: 1)
limit: Số lượng kết quả mỗi trang (mặc định: 10)
```

**Success Response:**
```json
{
  "status": "success",
  "code": 200,
  "success": true,
  "message": "Lấy danh sách trạng thái học thành công",
  "data": {
    "learn": {
      "items": [
        {
          "id": "uuid",
          "user_id": "uuid",
          "word_id": "uuid",
          "status": "learning",
          "created_at": "datetime",
          "updated_at": "datetime",
          "word": "example",
          "pos": "n",
          "phonetic_text": "/ɪɡˈzɑːmpl/",
          "audio_url": "https://res.cloudinary.com/...",
          "image_url": "https://res.cloudinary.com/..."
        }
      ],
      "total": 5,
      "page": 1,
      "limit": 10,
      "total_pages": 1
    }
  }
}
```

### Get Learn Status of Word

Lấy trạng thái học của một từ cụ thể

- **URL:** `/api/learn/:wordId`
- **Method:** `GET`
- **Auth Required:** Yes
- **Permissions Required:** User

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
```json
{
  "status": "success",
  "code": 200,
  "success": true,
  "message": "Lấy trạng thái học thành công",
  "data": {
    "learn": {
      "id": "uuid",
      "user_id": "uuid",
      "word_id": "uuid",
      "status": "learning",
      "created_at": "datetime",
      "updated_at": "datetime",
      "word": "example",
      "pos": "n",
      "phonetic_text": "/ɪɡˈzɑːmpl/"
    }
  }
}
```

**Error Response:**
```json
{
  "status": "error",
  "code": 404,
  "success": false,
  "message": "Không tìm thấy thông tin học từ này"
}
```

### Create Learn Status

Tạo mới trạng thái học cho một từ

- **URL:** `/api/learn`
- **Method:** `POST`
- **Auth Required:** Yes
- **Permissions Required:** User

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "word_id": "uuid-of-word",
  "status": "learning" // Có thể là "learning", "learned" hoặc "skip"
}
```

**Success Response:**
```json
{
  "status": "success",
  "code": 201,
  "success": true,
  "message": "Tạo trạng thái học thành công",
  "data": {
    "learn": {
      "id": "uuid",
      "user_id": "uuid",
      "word_id": "uuid",
      "status": "learning",
      "created_at": "datetime",
      "updated_at": "datetime",
      "word": "example",
      "pos": "n",
      "phonetic_text": "/ɪɡˈzɑːmpl/"
    }
  }
}
```

**Error Response:**
```json
{
  "status": "error",
  "code": 400,
  "success": false,
  "message": "Word ID không hợp lệ"
}
```

### Update Learn Status

Cập nhật trạng thái học của một từ

- **URL:** `/api/learn/:wordId`
- **Method:** `PUT`
- **Auth Required:** Yes
- **Permissions Required:** User

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "status": "learned" // Có thể là "learning", "learned" hoặc "skip"
}
```

**Success Response:**
```json
{
  "status": "success",
  "code": 200,
  "success": true,
  "message": "Cập nhật trạng thái học thành công",
  "data": {
    "learn": {
      "id": "uuid",
      "user_id": "uuid",
      "word_id": "uuid",
      "status": "learned",
      "created_at": "datetime",
      "updated_at": "datetime",
      "word": "example",
      "pos": "n",
      "phonetic_text": "/ɪɡˈzɑːmpl/"
    }
  }
}
```

**Error Response:**
```json
{
  "status": "error",
  "code": 400,
  "success": false,
  "message": "Status không hợp lệ"
}
```

### Delete Learn Status

Xóa trạng thái học của một từ

- **URL:** `/api/learn/:wordId`
- **Method:** `DELETE`
- **Auth Required:** Yes
- **Permissions Required:** User

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
```json
{
  "status": "success",
  "code": 200,
  "success": true,
  "message": "Xóa trạng thái học thành công",
  "data": null
}
```

**Error Response:**
```json
{
  "status": "error",
  "code": 404,
  "success": false,
  "message": "Không tìm thấy thông tin học từ này"
}
```

## Upload API

// ... existing code ...

### Create Learn Status

Tạo mới trạng thái học cho một từ

- **URL:** `/api/users/:userId/learn`
- **Method:** `POST`
- **Auth Required:** Yes
- **Permissions Required:** User hoặc Admin

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "word_id": "uuid-of-word",
  "status": "learning" // Có thể là "learning", "learned" hoặc "skip"
}
```

**Success Response:**
```json
{
  "status": "success",
  "code": 201,
  "success": true,
  "message": "Tạo trạng thái học thành công",
  "data": {
    "learn": {
      "id": "uuid",
      "user_id": "uuid",
      "word_id": "uuid",
      "status": "learning",
      "created_at": "datetime",
      "updated_at": "datetime",
      "word": "example",
      "pos": "n",
      "phonetic_text": "/ɪɡˈzɑːmpl/"
    }
  }
}
```

**Error Response:**
```json
{
  "status": "error",
  "code": 400,
  "success": false,
  "message": "Word ID không hợp lệ"
}
```

// ... existing code ... 