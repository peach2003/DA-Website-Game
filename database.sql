CREATE DATABASE game_portal;
USE game_portal;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id)
);

INSERT INTO games (title, description, url) VALUES
('Flappy Bird', 'Game chim bay nổi tiếng!', 'games/flappybird/index.html'),
('2048', 'Trò chơi ghép số 2048', 'games/2048/index.html'),
('Pacman', 'Game cổ điển Pacman', 'games/pacman/index.html'),
('Snake', 'Game rắn săn mồi cổ điển', 'games/snake/index.html'),
('Breakout', 'Game phá gạch kinh điển', 'games/breakout/index.html');