-- 1. Tạo database
CREATE DATABASE IF NOT EXISTS brainy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brainy;

-- 2. Tạo bảng users (bảng cơ sở)
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tạo bảng cloudinary_files
CREATE TABLE cloudinary_files (
    id CHAR(36) PRIMARY KEY,
    owner_id CHAR(36) NOT NULL,
    owner_type ENUM('User', 'Word', 'Category', 'Lesson') NOT NULL,
    file_type ENUM('image', 'audio', 'video', 'document') NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    public_id VARCHAR(255) UNIQUE NOT NULL,
    format VARCHAR(50) NULL,
    metadata JSON NULL,
    status ENUM('active', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tạo bảng categories
CREATE TABLE categories (
    id CHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'active',
    order_index INT DEFAULT 0,
    progress INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tạo bảng lessons
CREATE TABLE lessons (
    id CHAR(36) PRIMARY KEY,
    category_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    content TEXT,
    cloudinary_file_id CHAR(36) NULL,
    status VARCHAR(50) DEFAULT 'active',
    order_index INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (cloudinary_file_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tạo bảng words
CREATE TABLE words (
    id CHAR(36) PRIMARY KEY,
    lesson_id CHAR(36) NULL,
    word VARCHAR(255) NOT NULL,
    pos VARCHAR(50),
    phonetic VARCHAR(255),
    phonetic_text VARCHAR(255),
    phonetic_am VARCHAR(255),
    phonetic_am_text VARCHAR(255),
    audio_id CHAR(36) NULL,
    image_id CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    FOREIGN KEY (audio_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL,
    FOREIGN KEY (image_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tạo bảng senses
CREATE TABLE senses (
    id CHAR(36) PRIMARY KEY,
    word_id CHAR(36) NOT NULL,
    definition TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tạo bảng examples
CREATE TABLE examples (
    id CHAR(36) PRIMARY KEY,
    sense_id CHAR(36) NOT NULL,
    cf VARCHAR(255),
    x TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sense_id) REFERENCES senses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tạo bảng user_progress
CREATE TABLE user_progress (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    word_id CHAR(36) NOT NULL,
    status ENUM('new', 'learning', 'mastered') DEFAULT 'new',
    last_review TIMESTAMP NULL,
    next_review TIMESTAMP NULL,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tạo bảng learn
CREATE TABLE learn (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    word_id CHAR(36) NOT NULL,
    status ENUM('skip', 'learned', 'learning') DEFAULT 'learning',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_word (user_id, word_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tạo bảng user_notes
CREATE TABLE user_notes (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    word_id CHAR(36) NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Tạo bảng password_resets
CREATE TABLE password_resets (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Tạo bảng refresh_tokens
CREATE TABLE refresh_tokens (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Tạo các indexes
CREATE INDEX idx_word ON words(word);
CREATE INDEX idx_user_progress ON user_progress(user_id, word_id);
CREATE INDEX idx_user_notes ON user_notes(user_id, word_id);
CREATE INDEX idx_cloudinary_owner ON cloudinary_files(owner_id, owner_type);
CREATE INDEX idx_lesson_category ON lessons(category_id);
CREATE INDEX idx_word_lesson ON words(lesson_id);
CREATE INDEX idx_learn ON learn(user_id, status);

-- 15. Tạo triggers cho UUID
DELIMITER //

CREATE TRIGGER before_insert_users
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_cloudinary_files
BEFORE INSERT ON cloudinary_files
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_categories
BEFORE INSERT ON categories
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_lessons
BEFORE INSERT ON lessons
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_words
BEFORE INSERT ON words
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_senses
BEFORE INSERT ON senses
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_examples
BEFORE INSERT ON examples
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_user_progress
BEFORE INSERT ON user_progress
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_user_notes
BEFORE INSERT ON user_notes
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_password_resets
BEFORE INSERT ON password_resets
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_refresh_tokens
BEFORE INSERT ON refresh_tokens
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

CREATE TRIGGER before_insert_learn
BEFORE INSERT ON learn
FOR EACH ROW
BEGIN
    IF NEW.id IS NULL THEN
        SET NEW.id = UUID();
    END IF;
END //

DELIMITER ;
