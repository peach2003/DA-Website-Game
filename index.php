<?php
session_start();
include 'config.php';

// Hàm tạo URL ảnh game dựa trên tên game
function getGameImage($title)
{
    $gameImages = [
        'pacman' => 'https://th.bing.com/th/id/R.5e2f2399be25799401513ea1fcbac5ba?rik=avMBew%2bAzW27Og&riu=http%3a%2f%2fcdn.cnn.com%2fcnnnext%2fdam%2fassets%2f200518114838-05-pac-man-40.jpg&ehk=rDP9NuHnVcOvu8xJW0I4ADgb%2fnsMQk8r5wA5eUNHZtA%3d&risl=&pid=ImgRaw&r=0',
        'flappybird' => 'https://danviet.mediacdn.vn/296231569849192448/2023/8/5/hanh-trinh-flappy-bird-nguyen-ha-dong-1691264866444132753304.jpeg',
        'snake' => 'https://play-lh.googleusercontent.com/v8w4fmZli_DKkn9tN5P_tr0Wvky4zVjSf0pmd9VATiRJV-yfpI9cDwnpAMmpMh3tz94',
        'breakout' => 'https://th.bing.com/th/id/OIP.UX9SuvFo0NsIKUgHAYxIuQHaFj?rs=1&pid=ImgDetMain'
    ];

    // Chuyển tên game thành chữ thường và bỏ khoảng trắng
    $cleanTitle = strtolower(str_replace(' ', '', $title));

    // Trả về URL ảnh tương ứng hoặc ảnh mặc định
    return isset($gameImages[$cleanTitle])
        ? $gameImages[$cleanTitle]
        : 'https://img.freepik.com/premium-vector/video-game-controller-neon-sign-night-bright-advertisement_99087-158.jpg';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Roboto', sans-serif;
    }

    body {
        background: #f0f2f5;
        color: #1a1a1a;
    }

    .header {
        background: #ffffff;
        padding: 1rem 2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header h1 {
        color: #1a1a1a;
        font-size: 1.5rem;
    }

    .auth-buttons a {
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        margin-left: 10px;
    }

    .login-btn {
        background: #4CAF50;
        color: white;
    }

    .register-btn {
        border: 1px solid #4CAF50;
        color: #4CAF50;
    }

    .logout-btn {
        background: #f44336;
        color: white;
    }

    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 2rem;
        padding: 1rem;
    }

    .game-card {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        aspect-ratio: 1;
        cursor: pointer;
        text-decoration: none;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .game-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
        background: #1a1a1a;
        padding: 20px;
    }

    .game-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1rem;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
        color: white;
        font-size: 1.2rem;
        text-align: center;
        transform: translateY(0);
        transition: transform 0.3s ease;
    }

    .game-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .play-text {
        color: white;
        font-size: 1.2rem;
        font-weight: bold;
        padding: 0.8rem 2rem;
        border: 2px solid rgba(255, 255, 255, 0.8);
        border-radius: 30px;
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(20px);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .play-text:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent);
        transition: 0.5s;
    }

    .game-card:hover .game-overlay {
        opacity: 1;
    }

    .game-card:hover .play-text {
        transform: translateY(0);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
    }

    .game-card:hover .play-text:before {
        left: 100%;
    }

    .game-card:hover .game-image {
        transform: scale(1.05);
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .game-card:hover .play-text {
        animation: pulse 2s infinite;
    }

    .section-title {
        font-size: 2.5rem;
        margin: 2rem 0;
        text-align: center;
        color: #1a1a1a;
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }

        .game-title {
            font-size: 1rem;
        }

        .play-text {
            font-size: 1.2rem;
            padding: 0.8rem 1.6rem;
        }
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>Chào mừng, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Khách'; ?>!</h1>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="logout-btn">Đăng xuất</a>
            <?php else: ?>
            <a href="login.php" class="login-btn">Đăng nhập</a>
            <a href="register.php" class="register-btn">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title">Danh sách game</h2>

        <div class="games-grid">
            <?php
            if (!$conn) {
                die("Lỗi kết nối database: " . mysqli_connect_error());
            }

            $result = $conn->query("SELECT * FROM games");

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $gameImage = getGameImage($row['title']);
                    ?>
            <a href="game.php?id=<?php echo $row['id']; ?>" class="game-card">
                <img src="<?php echo $gameImage; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>"
                    class="game-image">
                <h3 class="game-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                <div class="game-overlay">
                    <div class="play-text">Chơi Ngay</div>
                </div>
            </a>
            <?php
                endwhile;
            else:
                ?>
            <p>Chưa có game nào trong hệ thống.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
