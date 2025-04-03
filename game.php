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
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($game['title']); ?></title>
    <style>
    body {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    #game-container {
        width: 70%;
    }

    #leaderboard-container {
        width: 28%;
        padding: 20px;
        background: #f5f5f5;
        border-radius: 8px;
    }

    iframe {
        border: none;
        width: 100%;
        height: 600px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #4CAF50;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    #score-display {
        font-size: 24px;
        margin: 20px 0;
        padding: 10px;
        background: #e8f5e9;
        border-radius: 4px;
    }

    #saveScoreBtn {
        display: none;
        padding: 10px 20px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #saveScoreBtn:hover {
        background: #45a049;
    }
    </style>
</head>

<body>
    <!-- Cột hiển thị game -->
    <div id="game-container">
        <h1><?php echo htmlspecialchars($game['title']); ?></h1>

        <!-- Nhúng game -->
        <iframe id="gameFrame" src="<?php echo htmlspecialchars($game['url']); ?>"></iframe>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div id="score-display">
            <b>Điểm của bạn:</b> <span id="scoreDisplay">0</span>
            <button id="saveScoreBtn" onclick="submitScore()">Lưu điểm</button>
        </div>
        <?php else: ?>
        <p><b>Đăng nhập để lưu điểm của bạn!</b></p>
        <a href="login.php">Đăng nhập</a>
        <?php endif; ?>
    </div>

    <!-- Cột hiển thị bảng xếp hạng -->
    <div id="leaderboard-container">
        <h2>Bảng Xếp Hạng</h2>
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

    <script>
    let gameFrame = document.getElementById('gameFrame');
    let currentGameId = <?php echo $game_id; ?>;
    let lastScore = 0;

    // Hàm gửi điểm về server
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

    // Hàm cập nhật điểm hiển thị
    function updateScoreDisplay(score) {
        if (!isNaN(score) && score > 0 && score > lastScore) {
            lastScore = score;
            document.getElementById('scoreDisplay').innerText = score;
            document.getElementById('saveScoreBtn').style.display = 'inline-block';
        }
    }

    // Khởi tạo khi game load xong
    gameFrame.onload = function() {
        try {
            const gameDoc = gameFrame.contentDocument || gameFrame.contentWindow.document;
            const gameWindow = gameFrame.contentWindow;

            // Script để theo dõi điểm cho các game
            const script = gameDoc.createElement('script');
            script.textContent = `
                // Kiểm tra và sử dụng phương thức getScore nếu có
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

            // Lắng nghe điểm từ game
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