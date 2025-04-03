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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng games
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(255) NOT NULL,
    category_id INT, -- khóa ngoại đến categories
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Bảng category
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
    category_id INT, -- khóa ngoại đến categories
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Bảng tag
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Bảng trung gian game_tag (many-to-many giữa games và tags)
CREATE TABLE game_tag (
    game_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (game_id, tag_id),
    FOREIGN KEY (game_id) REFERENCES games(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);

-- Bảng scores
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id)
);

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

INSERT INTO tags (name) VALUES
('1 người chơi'), ('2 người chơi'), ('3 người chơi'), ('3D'), ('Amongus.io'),
('Arcade'), ('Âm nhạc'), ('Ảnh đẹp nhất'), ('Baby Cathy'), ('Baby Hazel'),
('Bắn nhau'), ('Bắn súng'), ('Barbie'), ('Bạo loạn'), ('Bãi đậu xe'),
('Bắn cung'), ('Bắn pháo'), ('Bắn súng cổ điển'), ('Bắn xe tăng'), ('Bloxorz'),
('Bubble Shooter'), ('Bóng đá'), ('Bóng rổ'), ('Chạy nhảy'), ('Cá mập'),
('Câu đố'), ('Cảnh sát'), ('Công chúa'), ('Chiến cơ'), ('Chiến đấu'),
('Chiến lược'), ('Chơi cùng bạn'), ('Chơi đơn'), ('Chọc phá'), ('Chú khỉ buồn'),
('Cờ vua'), ('Cờ tướng'), ('Đánh cờ'), ('Đua xe'), ('Dragon Ball Z'),
('Dễ thương'), ('Động vật'), ('Dọn dẹp'), ('Đua mô tô'), ('Đua thuyền'),
('Đua xe địa hình'), ('Đua xe ô tô'), ('Đua xe tốc độ'), ('Elsa'), ('Escape'),
('FNAF'), ('Fashion'), ('Flappy Bird'), ('GTA'), ('Giải đố'),
('Giáng Sinh'), ('Giết thời gian'), ('Halloween'), ('Hành động'),
('Hậu tận thế'), ('Hóa giải'), ('Hôn nhau'), ('HTML5'), ('Học'),
('Hôn'), ('IQ'), ('Khủng long'), ('Kiếm hiệp'), ('Kinh dị'), ('Kỳ nghỉ'),
('Lái xe'), ('Làm bánh'), ('Làm tóc'), ('Làm đẹp'), ('Lễ hội'),
('Lửa và nước'), ('Lửa vs nước'), ('Lửa và băng'), ('Ma'), ('Manga'),
('Mario'), ('Màu sắc'), ('Máy tính'), ('Minecraft'), ('Mini game'),
('Mô phỏng'), ('Mỹ nhân'), ('Nấu ăn'), ('Người nhện'), ('Người que'),
('Người que chiến đấu'), ('Người tuyết'), ('Nhà'), ('Ngân hàng'),
('Ngôi sao'), ('Ngựa'), ('Nhà 3'), ('Ninja'), ('Ninja rùa'), ('Ngọc rồng'),
('Ninja chiến đấu'), ('Ngọc Rồng Siêu Cấp'), ('Ngọc trai đen'), ('One Piece'),
('Phá hoại'), ('Phiêu lưu'), ('Pixel'), ('Platform'), ('Pokemon'),
('Quái vật'), ('Quái vật khổng lồ'), ('Rắn săn mồi'), ('RPG'),
('Running Game'), ('Sát thủ'), ('Siêu nhân'), ('Sonic'), ('Spiderman'),
('Squid Game'), ('Sát thủ bóng tối'), ('Tay đua'), ('Tìm điểm khác nhau'),
('Thế giới mở'), ('Thời trang'), ('Thời trang công chúa'), ('Thế giới kẹo'),
('Tiệc trà'), ('Tiệc sinh nhật'), ('Tình yêu'), ('Tình bạn'),
('Tình yêu tuổi teen'), ('Trẻ em'), ('Trồng cây'), ('Trường học'),
('Trượt tuyết'), ('Tuyết rơi'), ('Tàu lượn'), ('Tàu thủy'), ('Tàu ngầm'),
('Tàu hỏa'), ('Tập vẽ'), ('Tết'), ('Thiết kế'), ('Thú cưng'),
('Thử thách'), ('Tính toán'), ('Trí tuệ'), ('Trí thông minh'),
('Tuyệt vời'), ('Vẽ'), ('Vui nhộn'), ('Xạ thủ'), ('Xếp hình'), ('Zombie'),
('Zuma'), ('Đầu bếp'), ('Đại dương'), ('Đánh nhau'), ('Đua xe tải'),
('Đua xe vượt địa hình'), ('Đơn giản'), ('Đơn giản nhất');

-- Game 1: Flappy Bird
INSERT INTO game_tag (game_id, tag_id) VALUES
(1, 1), -- Flappy Bird
(1, 2), -- Giết thời gian
(1, 3); -- 1 người chơi

-- Game 2: 2048
INSERT INTO game_tag (game_id, tag_id) VALUES
(2, 4), -- IQ
(2, 5), -- Trí tuệ
(2, 6); -- Giải đố

-- Game 3: Pacman
INSERT INTO game_tag (game_id, tag_id) VALUES
(3, 7), -- Arcade
(3, 8), -- Quái vật
(3, 9); -- Cổ điển

INSERT INTO games (title, description, url, category_id, thumbnail) VALUES
('Flappy Bird', 'Game chim bay nổi tiếng!', 'games/flappybird/index.html', 1, 'images/games/flappybird.jpg'),
('Pacman', 'Game cổ điển Pacman', 'games/pacman/index.html', 3, 'images/games/pacman.jpg'),
('Snake', 'Game rắn săn mồi cổ điển', 'games/snake/index.html', 3, 'images/games/snake.jpg'),
('Breakout', 'Game phá gạch kinh điển', 'games/breakout/index.html', 1, 'images/games/breakout.jpg');
