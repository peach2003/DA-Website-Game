CREATE DATABASE game_portal;
USE game_portal;

-- Bảng users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    gender enum('male','female','other') DEFAULT 'other',
    birthday date DEFAULT NULL,
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
    expires_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
    title VARCHAR(255) DEFAULT NULL,
    content TEXT DEFAULT NULL

);

-- Bảng games
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    url VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255),
    category_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Bảng tags
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    title varchar(255) DEFAULT NULL,
    content text DEFAULT NULL

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
INSERT INTO categories (id, name, title, content) VALUES
(1, 'Cho Con Gái', 'Thưởng thức những trò chơi sáng tạo và vui nhộn, được thiết kế đặc biệt cho các bạn gái.', 'Khám phá thời trang, trang điểm và phiêu lưu, nơi phong cách và trí tưởng tượng tỏa sáng.'),
(2, 'Đối Kháng', NULL, NULL),
(3, 'Bắn Nhau', 'Thử sức với những trò chơi bắn súng hành động kịch tính.', 'Từ bắn tỉa chính xác đến những trận chiến nảy lửa, mang đến trải nghiệm đầy phấn khích trong các bối cảnh đa dạng.'),
(4, 'Lái Xe', NULL, NULL),
(5, 'Chiến Thuật & Nhập Vai', NULL, NULL),
(6, 'Cân Não', NULL, NULL),
(7, 'Hành Động và Phiêu Lưu', NULL, NULL),
(8, 'Kỹ Năng', NULL, NULL),
(9, 'Arcade & Cổ Điển', NULL, NULL),
(10, 'Vui Vẻ & Điên Rồ', NULL, NULL),
(12, 'Thể Thao', NULL, NULL);

-- Thêm dữ liệu mẫu cho tags với màu sắc
INSERT INTO tags (id, name,title, content) VALUES
(1, '1 người chơi', 'Khám phá những trò chơi 1 người đầy kịch tính!', 'Đắm chìm vào những cuộc phiêu lưu đơn độc hấp dẫn và chinh phục thử thách một mình.'),
(2, '2 người chơi', NULL, NULL),
(3, '3 người chơi', NULL, NULL),
(4, '3D', NULL, NULL),
(5, 'Amongus.io', NULL, NULL),
(6, 'Arcade', NULL, NULL),
(7, 'Âm nhạc', NULL, NULL),
(8, 'Ảnh đẹp nhất', NULL, NULL),
(9, 'Baby Cathy', NULL, NULL),
(10, 'Baby Hazel', NULL, NULL),
(11, 'Bắn nhau', NULL, NULL),
(12, 'Bắn súng', NULL, NULL),
(13, 'Barbie', NULL, NULL),
(14, 'Bạo loạn', NULL, NULL),
(15, 'Bãi đậu xe', NULL, NULL),
(16, 'Bắn cung', NULL, NULL),
(17, 'Bắn pháo', NULL, NULL),
(18, 'Bắn súng cổ điển', NULL, NULL),
(19, 'Bắn xe tăng', NULL, NULL),
(20, 'Bloxorz', NULL, NULL),
(21, 'Bubble Shooter', NULL, NULL),
(22, 'Bóng đá', NULL, NULL),
(23, 'Bóng rổ', NULL, NULL),
(24, 'Chạy nhảy', NULL, NULL),
(25, 'Cá mập', NULL, NULL),
(26, 'Câu đố', NULL, NULL),
(27, 'Cảnh sát', NULL, NULL),
(28, 'Công chúa', NULL, NULL),
(29, 'Chiến cơ', NULL, NULL),
(30, 'Chiến đấu', NULL, NULL),
(31, 'Chiến lược', NULL, NULL),
(32, 'Chơi cùng bạn', NULL, NULL),
(33, 'Chơi đơn', NULL, NULL),
(34, 'Chọc phá', NULL, NULL),
(35, 'Chú khỉ buồn', NULL, NULL),
(36, 'Cờ vua', NULL, NULL),
(37, 'Cờ tướng', NULL, NULL),
(38, 'Đánh cờ', NULL, NULL),
(39, 'Đua xe', NULL, NULL),
(40, 'Dragon Ball Z', NULL, NULL),
(41, 'Dễ thương', NULL, NULL),
(42, 'Động vật', NULL, NULL),
(43, 'Dọn dẹp', NULL, NULL),
(44, 'Đua mô tô', NULL, NULL),
(45, 'Đua thuyền', NULL, NULL),
(46, 'Đua xe địa hình', NULL, NULL),
(47, 'Đua xe ô tô', NULL, NULL),
(48, 'Đua xe tốc độ', NULL, NULL),
(49, 'Elsa', NULL, NULL),
(50, 'Escape', NULL, NULL),
(51, 'FNAF', NULL, NULL),
(52, 'Fashion', NULL, NULL),
(53, 'Flappy Bird', NULL, NULL),
(54, 'GTA', NULL, NULL),
(55, 'Giải đố', NULL, NULL),
(56, 'Giáng Sinh', NULL, NULL),
(57, 'Giết thời gian', NULL, NULL),
(58, 'Halloween', NULL, NULL),
(59, 'Hành động', NULL, NULL),
(60, 'Hậu tận thế', NULL, NULL),
(61, 'Hóa giải', NULL, NULL),
(62, 'Hôn nhau', NULL, NULL),
(63, 'HTML5', NULL, NULL),
(64, 'Học', NULL, NULL),
(65, 'Hôn', NULL, NULL),
(66, 'IQ', NULL, NULL),
(67, 'Khủng long', NULL, NULL),
(68, 'Kiếm hiệp', NULL, NULL),
(69, 'Kinh dị', NULL, NULL),
(70, 'Kỳ nghỉ', NULL, NULL),
(71, 'Lái xe', NULL, NULL),
(72, 'Làm bánh', NULL, NULL),
(73, 'Làm tóc', NULL, NULL),
(74, 'Làm đẹp', NULL, NULL),
(75, 'Lễ hội', NULL, NULL),
(76, 'Lửa và nước', NULL, NULL),
(77, 'Lửa vs nước', NULL, NULL),
(78, 'Lửa và băng', NULL, NULL),
(79, 'Ma', NULL, NULL),
(80, 'Manga', NULL, NULL),
(81, 'Mario', NULL, NULL),
(82, 'Màu sắc', NULL, NULL),
(83, 'Máy tính', NULL, NULL),
(84, 'Minecraft', NULL, NULL),
(85, 'Mini game', NULL, NULL),
(86, 'Mô phỏng', NULL, NULL),
(87, 'Mỹ nhân', NULL, NULL),
(88, 'Nấu ăn', NULL, NULL),
(89, 'Người nhện', NULL, NULL),
(90, 'Người que', NULL, NULL),
(91, 'Người que chiến đấu', NULL, NULL),
(92, 'Người tuyết', NULL, NULL),
(93, 'Nhà', NULL, NULL),
(94, 'Ngân hàng', NULL, NULL),
(95, 'Ngôi sao', NULL, NULL),
(96, 'Ngựa', NULL, NULL),
(97, 'Nhà 3', NULL, NULL),
(98, 'Ninja', NULL, NULL),
(99, 'Ninja rùa', NULL, NULL),
(100, 'Ngọc rồng', NULL, NULL),
(101, 'Ninja chiến đấu', NULL, NULL),
(102, 'Ngọc Rồng Siêu Cấp', NULL, NULL),
(103, 'Ngọc trai đen', NULL, NULL),
(104, 'One Piece', NULL, NULL),
(105, 'Phá hoại', NULL, NULL),
(106, 'Phiêu lưu', NULL, NULL),
(107, 'Pixel', NULL, NULL),
(108, 'Platform', NULL, NULL),
(109, 'Pokemon', NULL, NULL),
(110, 'Quái vật', NULL, NULL),
(111, 'Quái vật khổng lồ', NULL, NULL),
(112, 'Rắn săn mồi', NULL, NULL),
(113, 'RPG', NULL, NULL),
(114, 'Running Game', NULL, NULL),
(115, 'Sát thủ', NULL, NULL),
(116, 'Siêu nhân', NULL, NULL),
(117, 'Sonic', NULL, NULL),
(118, 'Spiderman', NULL, NULL),
(119, 'Squid Game', NULL, NULL),
(120, 'Sát thủ bóng tối', NULL, NULL),
(121, 'Tay đua', NULL, NULL),
(122, 'Tìm điểm khác nhau', NULL, NULL),
(123, 'Thế giới mở', NULL, NULL),
(124, 'Thời trang', NULL, NULL),
(125, 'Thời trang công chúa', NULL, NULL),
(126, 'Thế giới kẹo', NULL, NULL),
(127, 'Tiệc trà', NULL, NULL),
(128, 'Tiệc sinh nhật', NULL, NULL),
(129, 'Tình yêu', NULL, NULL),
(130, 'Tình bạn', NULL, NULL),
(131, 'Tình yêu tuổi teen', NULL, NULL),
(132, 'Trẻ em', NULL, NULL),
(133, 'Trồng cây', NULL, NULL),
(134, 'Trường học', NULL, NULL),
(135, 'Trượt tuyết', NULL, NULL),
(136, 'Tuyết rơi', NULL, NULL),
(137, 'Tàu lượn', NULL, NULL),
(138, 'Tàu thủy', NULL, NULL),
(139, 'Tàu ngầm', NULL, NULL),
(140, 'Tàu hỏa', NULL, NULL),
(141, 'Tập vẽ', NULL, NULL),
(142, 'Tết', NULL, NULL),
(143, 'Thiết kế', NULL, NULL),
(144, 'Thú cưng', NULL, NULL),
(145, 'Thử thách', NULL, NULL),
(146, 'Tính toán', NULL, NULL),
(147, 'Trí tuệ', NULL, NULL),
(148, 'Trí thông minh', NULL, NULL),
(149, 'Tuyệt vời', NULL, NULL),
(150, 'Vẽ', NULL, NULL),
(151, 'Vui nhộn', NULL, NULL),
(152, 'Xạ thủ', NULL, NULL),
(153, 'Xếp hình', NULL, NULL),
(154, 'Zombie', NULL, NULL),
(155, 'Zuma', NULL, NULL),
(156, 'Đầu bếp', NULL, NULL),
(157, 'Đại dương', NULL, NULL),
(158, 'Đánh nhau', NULL, NULL),
(159, 'Đua xe tải', NULL, NULL),
(160, 'Đua xe vượt địa hình', NULL, NULL),
(161, 'Đơn giản', NULL, NULL),
(162, 'Đơn giản nhất', NULL, NULL);



-- Thêm dữ liệu mẫu cho games
INSERT INTO games (id, title, description, url, category_id, created_at, thumbnail) VALUES
(1, 'Flappy Bird', 'Game chim bay nổi tiếng!', 'games/flappybird/index.html', 1, '2025-04-02 09:07:02', 'flappybird.jpg'),
(2, 'Pacman', 'Game cổ điển Pacman', 'games/pacman/index.html', 3, '2025-04-02 09:07:02', 'images/games/pacman.jpg'),
(3, 'Snake', 'Game rắn săn mồi cổ điển', 'games/snake/index.html', 3, '2025-04-02 09:07:02', 'images/games/snake.jpg'),
(4, 'Breakout', 'Game phá gạch kinh điển', 'games/breakout/index.html', 1, '2025-04-02 09:07:02', 'images/games/breakout.jpg');

-- Thêm dữ liệu mẫu cho game_tag
INSERT INTO game_tag (game_id, tag_id) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 7),
(3, 8),
(3, 9);

INSERT INTO scores (id, user_id, game_id, score, created_at) VALUES
(1, 1, 1, 3, '2025-04-02 09:43:24'),
(8, 1, 3, 50, '2025-04-02 10:54:16'),
(13, 2, 1, 6, '2025-04-03 08:54:51'),
(39, 2, 2, 260, '2025-04-03 08:56:06'),
(45, 2, 3, 20, '2025-04-03 08:56:27');

