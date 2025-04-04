CREATE DATABASE game_portal;
USE game_portal;

-- Bảng users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng user_tokens
CREATE TABLE user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Bảng games
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Bảng tags
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
);

-- Bảng trung gian game_tag (many-to-many giữa games và tags)
CREATE TABLE game_tag (
    game_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (game_id, tag_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Bảng scores
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Thêm dữ liệu mẫu cho categories
INSERT INTO categories (name) VALUES
('Cho Con Gái'),
('Đối Kháng'),
('Bắn Nhau'),
('Lái Xe'),
('Chiến Thuật & Nhập Vai'),
('Cân Não'),
('Hành Động và Phiêu Lưu'),
('Kỹ Năng'),
('Arcade & Cổ Điển'),
('Vui Vẻ & Điên Rồ'),
('Quản lý & Sim'),
('Thể Thao');

-- Thêm dữ liệu mẫu cho tags với màu sắc
INSERT INTO tags (name, color) VALUES
('1 người chơi', '#4CAF50'),
('2 người chơi', '#2196F3'),
('3 người chơi', '#9C27B0'),
('3D', '#FF9800'),
('Amongus.io', '#E91E63'),
('Arcade', '#673AB7'),
('Âm nhạc', '#3F51B5'),
('Ảnh đẹp nhất', '#00BCD4'),
('Baby Cathy', '#009688'),
('Baby Hazel', '#8BC34A'),
('Bắn nhau', '#FF5722'),
('Bắn súng', '#795548'),
('Barbie', '#FF4081'),
('Bạo loạn', '#607D8B'),
('Bãi đậu xe', '#FFC107');

-- Thêm dữ liệu mẫu cho games
INSERT INTO games (title, description, url, category_id, thumbnail) VALUES
('Flappy Bird', 'Game chim bay nổi tiếng!', 'games/flappybird/index.html', 9, 'images/games/flappybird.jpg'),
('Pacman', 'Game cổ điển Pacman', 'games/pacman/index.html', 9, 'images/games/pacman.jpg'),
('Snake', 'Game rắn săn mồi cổ điển', 'games/snake/index.html', 9, 'images/games/snake.jpg'),
('Breakout', 'Game phá gạch kinh điển', 'games/breakout/index.html', 9, 'images/games/breakout.jpg');

-- Thêm dữ liệu mẫu cho game_tag
INSERT INTO game_tag (game_id, tag_id) VALUES
(1, 1), -- Flappy Bird - 1 người chơi
(1, 6), -- Flappy Bird - Arcade
(2, 1), -- Pacman - 1 người chơi
(2, 6), -- Pacman - Arcade
(3, 1), -- Snake - 1 người chơi
(3, 6), -- Snake - Arcade
(4, 1), -- Breakout - 1 người chơi
(4, 6); -- Breakout - Arcade
