<?php
session_start();
require_once 'config.php';
include 'refresh_token.php';
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
        padding: 20px;
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
    }

    .game-section {
        flex: 2;
        display: flex;
        flex-direction: column;
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
    }

    .game-title {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
        font-weight: bold;
    }

    iframe {
        border: none;
        width: 100%;
        height: 600px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .score-section {
        margin-top: 20px;
        padding: 15px;
        background: #fff;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .tags-title {
        font-size: 20px;
        margin-bottom: 15px;
        color: #333;
        text-align: center;
        font-weight: bold;
    }

    .tags-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tag {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 14px;
        color: #fff;
        text-decoration: none;
        background: #ff3e3e;
        transition: all 0.3s ease;
    }

    .tag:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background: #ff5555;
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
    </style>
</head>

<body>
    <div class="container">
        <!-- Phần game (2/3 bên trái) -->
        <div class="game-section">
            <div class="game-container">
                <h1 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h1>
                <iframe id="gameFrame" src="<?php echo htmlspecialchars($game['url']); ?>"></iframe>

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
                <h2 class="tags-title">Thẻ Game</h2>
                <div class="tags-grid">
                    <?php while ($tag = $tags_result->fetch_assoc()): ?>
                    <a href="tags.php?tag=<?php echo urlencode($tag['name']); ?>" class="tag">
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

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
    </script>
</body>

</html>