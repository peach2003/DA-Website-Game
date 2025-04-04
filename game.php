<?php
session_start();
require_once 'config.php';
include 'refresh_token.php';

include_once 'header.php';
// Kiểm tra và lọc game_id
$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$game_id) {
    die("ID game không hợp lệ!");
}

// Lấy thông tin game từ database và kiểm tra an toàn
$stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();

if (!$game) {
    die("Game không tồn tại!");
}

// Lấy top 10 điểm cao nhất cho game này
$scores_query = "
    SELECT u.username, s.score, s.created_at
    FROM scores s
    JOIN users u ON s.user_id = u.id
    WHERE s.game_id = ?
    ORDER BY s.score DESC
    LIMIT 10
";

$stmt = $conn->prepare($scores_query);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$scores_result = $stmt->get_result();

// Lấy các tag của game
$tags_query = "
SELECT t.name
FROM tags t
JOIN game_tag gt ON t.id = gt.tag_id
WHERE gt.game_id = ?
";
$stmt = $conn->prepare($tags_query);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$tags_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($game['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        color: #333;
        min-height: 100vh;
    }

    .container {
        display: flex;
        gap: 20px;
        max-width: 1600px;
        margin: 0 auto;
        padding: 120px 20px 20px 20px;
        position: relative;
        background-color: #f5f5f5;
        z-index: 1;
    }

    /* Thêm media query để điều chỉnh padding trên màn hình nhỏ */
    @media (max-width: 768px) {
        .container {
            padding-top: 100px;
            /* Giảm padding-top trên mobile */
        }
    }


    .game-section {
        flex: 2;
        display: flex;
        flex-direction: column;
        background-color: #f5f5f5;
    }

    .sidebar {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .game-container {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Căn giữa nội dung */
    }

    .game-title {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
        font-weight: bold;
    }



    .score-section {
        margin-top: 20px;
        padding: 10px;
        background: #fff;
        border-radius: 10px;
        text-align: start;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 50px
    }

    #score-display {
        font-size: 24px;
        margin: 10px 0;
        color: #333;
    }

    #saveScoreBtn {
        display: none;
        padding: 12px 25px;
        background: #ff3e3e;
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    #saveScoreBtn:hover {
        background: #ff5555;
        transform: translateY(-2px);
    }

    .leaderboard-container {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .leaderboard-title {
        font-size: 20px;
        margin-bottom: 15px;
        color: #333;
        text-align: center;
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
        color: #333;
        border-bottom: 1px solid #eee;
    }

    th {
        background: #ff3e3e;
        color: #fff;
        font-weight: 600;
    }

    tr:hover {
        background: #f8f8f8;
    }

    .tags-container {
        background: #fff;
        padding: 20px;
        border-radius: 20px;
    }

    .tags-title {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
        font-weight: bold;
        padding-left: 10px;
    }

    .tags-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 0 10px;
    }

    .tag {
        display: inline-block;
        padding: 13px 16px;
        border-radius: 15px;
        font-size: 15px;
        color: #333;
        text-decoration: none;
        background: #fff;
        transition: all 0.3s ease;
        border: 2px solid;
        font-weight: 500;
    }

    .tag:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .login-prompt {
        text-align: center;
        margin-top: 20px;
    }

    .login-prompt a {
        color: #ff3e3e;
        text-decoration: none;
        font-weight: bold;
    }

    .login-prompt a:hover {
        color: #ff5555;
        text-decoration: underline;
    }

    @media (max-width: 1200px) {
        .container {
            flex-direction: column;
        }

        .game-section,
        .sidebar {
            flex: 1;
        }
    }

    .game-frame-wrapper {
        position: relative;
        width: 100%;
        border-radius: 15px;
        overflow: hidden;
        margin: 0 auto;
        aspect-ratio: 4/3;
        /* Thêm tỷ lệ khung hình cố định */
    }

    iframe,
    #gameFrame {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .title-row {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .game-title {
        font-size: 28px;
        margin: 0;
        color: #333;
        flex: 1;
    }

    .fullscreen-btn {
        background: #f0f0f0;
        border: none;
        border-radius: 8px;
        padding: 8px 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #333;
        font-size: 16px;
    }

    .fullscreen-btn:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .game-info {
        background: white;
        border-radius: 15px;
        padding: 10px;
        margin-bottom: 10px;

        width: 100%;
    }

    .game-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .title-section {
        flex: 1;
    }

    .game-title {
        font-size: 28px;
        margin: 0 0 10px 0;
        color: #333;
    }

    .game-meta {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .play-count {
        color: #666;
        font-size: 15px;
    }

    .game-rating {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stars {
        color: #ffd700;
        font-size: 16px;
    }

    .rating-actions {
        display: flex;
        gap: 10px;
    }

    .rating-btn {
        background: #f0f0f0;
        border: none;
        border-radius: 8px;
        padding: 8px 15px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .rating-btn:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }

    .rating-btn.like {
        color: #4CAF50;
    }

    .rating-btn.dislike {
        color: #f44336;
    }

    .game-description {
        background: white;
        border-radius: 15px;
        padding: 10px;
        width: 100%;
    }

    .game-description h2 {
        color: #333;
        font-size: 20px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .game-description p {
        color: #666;
        line-height: 1.6;
        font-size: 15px;
    }

    #saveScoreBtn {
        background: #ff3e3e;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 12px 25px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: none;
    }

    #saveScoreBtn:hover {
        background: #ff5555;
        transform: translateY(-2px);
    }

    /* Style cho phần game đề xuất */
    .suggested-games {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-top: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 20px;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #ff3e3e;
    }

    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    .game-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .game-link {
        text-decoration: none;
        color: inherit;
    }

    .game-thumb {
        position: relative;
        padding-top: 56.25%;
        /* Tỷ lệ 16:9 */
        overflow: hidden;
    }

    .game-thumb img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .game-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .game-overlay i {
        color: white;
        font-size: 3em;
    }

    .game-card:hover .game-overlay {
        opacity: 1;
    }

    .game-info {
        padding: 12px;
    }

    .game-card-title {
        font-size: 16px;
        margin: 0 0 8px 0;
        color: #333;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .game-stats {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 14px;
        color: #666;
    }

    .game-stats i {
        color: #ff3e3e;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }

    /* Style cho phần game đề xuất */
    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 25px;
        padding: 10px;
    }

    .game-card {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        background: #fff;
        aspect-ratio: 16/9;
        /* Thay đổi từ height cố định sang tỷ lệ khung hình */
    }

    .game-link {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }

    .game-thumb {
        position: relative;
        width: 100%;
        height: 100%;
        background: #fff;
        /* Đổi background từ đen sang trắng */
    }

    .game-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Thay đổi từ contain sang cover */
        transition: transform 0.5s ease;
    }

    .game-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom,
                rgba(0, 0, 0, 0) 0%,
                rgba(0, 0, 0, 0.4) 50%,
                rgba(0, 0, 0, 0.8) 100%);
        /* Điều chỉnh gradient để nhẹ nhàng hơn */
        display: flex;
        align-items: flex-end;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .game-card-title {
        color: white;
        font-size: 15px;
        text-align: center;
        padding: 15px;
        margin: 0;
        width: 100%;
        transform: translateY(20px);
        transition: transform 0.3s ease;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
    }

    /* Hiệu ứng hover */
    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .game-card:hover .game-thumb img {
        transform: scale(1.05);
        /* Giảm scale để tránh ảnh bị cắt quá nhiều */
    }

    .game-card:hover .game-overlay {
        opacity: 1;
    }

    .game-card:hover .game-card-title {
        transform: translateY(0);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
        }

        .game-card-title {
            font-size: 14px;
            padding: 10px;
        }
    }

    /* Style cho phần game đề xuất */
    .games-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        /* Thay đổi thành 4 cột cố định */
        gap: 20px;
        padding: 10px;
        justify-items: center;
        /* Căn giữa các card trong grid */
    }

    .game-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        background: #fff;
        width: 180px;
        /* Giảm kích thước để vừa 4 card một hàng */
        height: 180px;
        aspect-ratio: 1/1;
    }

    .game-link {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }

    .game-thumb {
        position: relative;
        width: 100%;
        height: 100%;
        background: #f5f5f5;
        overflow: hidden;
    }

    .game-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .games-grid {
            grid-template-columns: repeat(3, 1fr);
            /* 3 cột trên tablet */
        }
    }

    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
            /* 2 cột trên mobile */
            gap: 15px;
        }

        .game-card {
            width: 150px;
            height: 150px;
        }
    }

    @media (max-width: 480px) {
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
            /* Giữ 2 cột trên mobile nhỏ */
        }

        .game-card {
            width: 130px;
            height: 130px;
        }
    }

    .promo-banner {
        background: linear-gradient(120deg, #4e54ff 0%, #9f6bff 100%);
        border-radius: 25px;
        padding: 30px 18px;
        color: white;
        display: flex;
        align-items: center;
        gap: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .promo-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg, #9f6bff 0%, #4e54ff 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .promo-banner:hover::before {
        opacity: 1;
    }

    .promo-banner:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(78, 84, 255, 0.2);
    }

    .promo-banner__icon {
        font-size: 32px;
        color: white;
        z-index: 1;
    }

    .promo-banner__content {
        z-index: 1;
    }

    .promo-banner__title {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #fff;
    }

    .promo-banner__subtitle {
        font-size: 17px;
        color: rgba(255, 255, 255, 0.9);
    }

    /* Animation cho icon */
    .promo-banner:hover .promo-banner__icon {
        animation: bounce 0.5s ease;
    }

    @keyframes bounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Phần game (2/3 bên trái) -->
        <div class="game-section">
            <div class="game-container">
                <!-- Phần iframe game với nút fullscreen -->
                <div class="game-frame-wrapper">
                    <iframe id="gameFrame" src="<?php echo htmlspecialchars($game['url']); ?>"></iframe>

                </div>

                <!-- Phần thông tin game -->
                <div class="game-info">
                    <div class="game-header">
                        <div class="title-section">
                            <div class="title-row">
                                <h1 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h1>
                                <button id="fullscreenBtn" onclick="toggleFullscreen()" class="fullscreen-btn">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                            <div class="game-meta">
                                <span class="play-count">
                                    <i class="fas fa-gamepad"></i>
                                    <?php echo rand(1000, 10000); ?> lượt chơi
                                </span>
                                <div class="game-rating">
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <div class="rating-actions">
                                        <button class="rating-btn like"><i class="fas fa-thumbs-up"></i></button>
                                        <button class="rating-btn dislike"><i class="fas fa-thumbs-down"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Phần điểm số -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="score-section">
                        <div id="score-display">
                            <b>Điểm của bạn:</b> <span id="scoreDisplay">0</span>
                        </div>
                        <button id="saveScoreBtn" onclick="submitScore()">
                            <i class="fas fa-save"></i> Lưu điểm
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="login-prompt">
                        <p><b>Đăng nhập để lưu điểm của bạn!</b></p>
                        <a href="login.php">Đăng nhập ngay</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Phần mô tả game -->
                <div class="game-description">
                    <h2><i class="fas fa-info-circle"></i> MÔ TẢ GAME</h2>
                    <p><?php echo nl2br(htmlspecialchars($game['description'])); ?></p>
                </div>
            </div>
            <!-- Thêm phần game đề xuất -->
            <div class="suggested-games">
                <h2 class="section-title">
                    <i class="fas fa-gamepad"></i> GAME CÙNG THỂ LOẠI
                </h2>
                <div class="games-grid">
                    <?php
                    // Lấy category_id của game hiện tại
                    $category_id = $game['category_id'];
                    $current_game_id = $game['id'];

                    // Query để lấy 10 game ngẫu nhiên cùng category, loại trừ game hiện tại
                    $similar_games_query = "
            SELECT g.id, g.title, g.thumbnail 
            FROM games g 
            WHERE g.category_id = ? 
            AND g.id != ? 
            ORDER BY RAND() 
            LIMIT 10
        ";

                    $stmt = $conn->prepare($similar_games_query);
                    $stmt->bind_param("ii", $category_id, $current_game_id);
                    $stmt->execute();
                    $similar_games = $stmt->get_result();

                    if ($similar_games->num_rows > 0):
                        while ($similar_game = $similar_games->fetch_assoc()):
                            // Lấy hình ảnh cho game đề xuất
                            $image_path = './assets/image_games/' . strtolower(str_replace(' ', ' ', $similar_game['title'])) . '.jpg';
                            $thumbnail = file_exists($image_path) ? $image_path : './assets/image_games/default-game.png';
                            ?>
                    <div class="game-card">
                        <a href="game.php?id=<?php echo $similar_game['id']; ?>" class="game-link">
                            <div class="game-thumb">
                                <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                                    alt="<?php echo htmlspecialchars($similar_game['title']); ?>" loading="lazy">
                                <div class="game-overlay">
                                    <h3 class="game-card-title">
                                        <?php echo htmlspecialchars($similar_game['title']); ?>
                                    </h3>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                        endwhile;
                    else:
                        ?>
                    <div class="no-games-message">
                        <p>Không có game tương tự trong thể loại này.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3 bên phải) -->
        <div class="sidebar">
            <!-- Bảng xếp hạng -->
            <div class="leaderboard-container">
                <h2 class="leaderboard-title">Bảng Xếp Hạng</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Hạng</th>
                            <th>Người chơi</th>
                            <th>Điểm</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardBody">
                        <?php
                        $rank = 1;
                        while ($row = $scores_result->fetch_assoc()):
                            ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo number_format($row['score']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tags -->
            <div class="tags-container">
                <h2 class="tags-title">GẮN THẺ</h2>
                <div class="tags-grid">
                    <?php
                    $usedColors = array();

                    function getRandomColor($usedColors)
                    {
                        $colors = array(
                            '#ff8c66', // Cam nhạt 
                            '#ff66b3', // Hồng 
                            '#cc66ff', // Tím 
                            '#66ff99', // Xanh lá 
                            '#66ccff', // Xanh lam 
                            '#ff6666', // Đỏ 
                            '#ffcc66'  // Vàng 
                        );

                        $availableColors = array_diff($colors, $usedColors);
                        if (empty($availableColors)) {
                            $usedColors = array(); // Reset used colors if all are used
                            $availableColors = $colors;
                        }

                        $color = array_rand(array_flip($availableColors));
                        $usedColors[] = $color;
                        return $color;
                    }

                    while ($tag = $tags_result->fetch_assoc()):
                        $borderColor = getRandomColor($usedColors);
                        ?>
                    <a href="tags.php?tag=<?php echo urlencode($tag['name']); ?>" class="tag"
                        style="border-color: <?php echo $borderColor; ?>">
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="promo-banner">
                <div class="promo-banner__icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <div class="promo-banner__content">
                    <div class="promo-banner__title">
                        Thêm game này vào trang web của bạn!
                    </div>
                    <div class="promo-banner__subtitle">
                        Bằng cách nhúng dòng mã đơn giản
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include_once 'footer.php';
    ?>

    <script>
    let gameFrame = document.getElementById('gameFrame');
    let currentGameId = <?php echo $game_id; ?>;
    let lastScore = 0;

    function submitScore() {
        let score = parseInt(document.getElementById('scoreDisplay').innerText);

        fetch('submit_score.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `game_id=${currentGameId}&score=${score}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.error || 'Có lỗi xảy ra khi lưu điểm');
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Có lỗi xảy ra khi lưu điểm');
            });
    }

    function updateScoreDisplay(score) {
        if (!isNaN(score) && score > 0 && score > lastScore) {
            lastScore = score;
            document.getElementById('scoreDisplay').innerText = score;
            document.getElementById('saveScoreBtn').style.display = 'inline-block';
        }
    }

    gameFrame.onload = function() {
        try {
            const gameDoc = gameFrame.contentDocument || gameFrame.contentWindow.document;
            const gameWindow = gameFrame.contentWindow;

            const script = gameDoc.createElement('script');
            script.textContent = `
                if (typeof window.getScore === 'function') {
                    setInterval(() => {
                        const score = window.getScore();
                        if (!isNaN(score) && score > 0) {
                            window.parent.postMessage(score, '*');
                        }
                    }, 1000);
                }
            `;
            gameDoc.body.appendChild(script);

            window.addEventListener('message', function(event) {
                const score = parseInt(event.data);
                if (!isNaN(score) && score > 0) {
                    updateScoreDisplay(score);
                }
            });

        } catch (e) {
            console.error('Lỗi khi khởi tạo game:', e);
        }
    };
    // Thêm function xử lý fullscreen
    function toggleFullscreen() {
        const gameFrame = document.getElementById('gameFrame');

        if (!document.fullscreenElement) {
            if (gameFrame.requestFullscreen) {
                gameFrame.requestFullscreen();
            } else if (gameFrame.webkitRequestFullscreen) {
                gameFrame.webkitRequestFullscreen();
            } else if (gameFrame.msRequestFullscreen) {
                gameFrame.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    }

    // Cập nhật icon khi thay đổi trạng thái fullscreen
    document.addEventListener('fullscreenchange', function() {
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        if (document.fullscreenElement) {
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
        }
    });
    </script>
</body>

</html>