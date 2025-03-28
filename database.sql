CREATE DATABASE IF NOT EXISTS brainy;
USE brainy;

-- Bảng users với UUID
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng cloudinary_files
CREATE TABLE cloudinary_files (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    owner_id UUID NOT NULL,
    owner_type ENUM('User', 'Word', 'Category', 'Lesson') NOT NULL,
    file_type ENUM('image', 'audio', 'video', 'document') NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    public_id VARCHAR(255) UNIQUE NOT NULL,
    format VARCHAR(50) NULL,
    metadata JSON NULL,
    status ENUM('active', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng categories
CREATE TABLE categories (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    progress INT DEFAULT 0,
    total INT NOT NULL,
    image_id UUID NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL
);

-- Bảng lessons
CREATE TABLE lessons (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    category_id UUID NOT NULL,
    title VARCHAR(255) NOT NULL,
    sub_title VARCHAR(255) NULL,
    cloudinary_file_id UUID NULL,
    order_index INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (cloudinary_file_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL
);

-- Bảng words (với UUID)
CREATE TABLE words (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    lesson_id UUID NULL,
    word VARCHAR(255) NOT NULL,
    pos VARCHAR(50),
    phonetic VARCHAR(255),
    phonetic_text VARCHAR(255),
    phonetic_am VARCHAR(255),
    phonetic_am_text VARCHAR(255),
    audio_id UUID NULL,
    image_id UUID NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    FOREIGN KEY (audio_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL,
    FOREIGN KEY (image_id) REFERENCES cloudinary_files(id) ON DELETE SET NULL
);

-- Bảng senses
CREATE TABLE senses (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    word_id UUID NOT NULL,
    definition TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
);

-- Bảng examples
CREATE TABLE examples (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    sense_id UUID NOT NULL,
    cf VARCHAR(255),
    x TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sense_id) REFERENCES senses(id) ON DELETE CASCADE
);

-- Bảng user_progress
CREATE TABLE user_progress (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    user_id UUID NOT NULL,
    word_id UUID NOT NULL,
    status ENUM('new', 'learning', 'mastered') DEFAULT 'new',
    last_review TIMESTAMP NULL,
    next_review TIMESTAMP NULL,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
);

-- Bảng user_notes
CREATE TABLE user_notes (
    id UUID PRIMARY KEY DEFAULT (UUID()),
    user_id UUID NOT NULL,
    word_id UUID NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
);

-- Tạo indexes
CREATE INDEX idx_word ON words(word);
CREATE INDEX idx_user_progress ON user_progress(user_id, word_id);
CREATE INDEX idx_user_notes ON user_notes(user_id, word_id);
CREATE INDEX idx_cloudinary_owner ON cloudinary_files(owner_id, owner_type);
CREATE INDEX idx_lesson_category ON lessons(category_id);
CREATE INDEX idx_word_lesson ON words(lesson_id);