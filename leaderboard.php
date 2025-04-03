<?php
session_start();
include 'config.php';
include 'refresh_token.php';
include 'header.php';

// Lấy game đầu tiên làm mặc định nếu không có game_id được chọn
$games_result = $conn->query("SELECT id, title FROM games ORDER BY id ASC LIMIT 1");
$default_game = $games_result->fetch_assoc();
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : $default_game['id'];

// Lấy danh sách game cho dropdown
$games_result = $conn->query("SELECT id, title FROM games");
$games = [];
while ($row = $games_result->fetch_assoc()) {
    $games[] = $row;
}

// Lấy thông tin user hiện tại
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Lấy top 50 điểm cao nhất
$scores_query = "
    SELECT 
        u.username, 
        s.score, 
        s.created_at,
        s.user_id,
        @rank := @rank + 1 as rank
    FROM 
        (SELECT @rank := 0) r,
        scores s
        JOIN users u ON s.user_id = u.id
    WHERE 
        s.game_id = ?
    ORDER BY 
        s.score DESC
    LIMIT 50
";

$stmt = $conn->prepare($scores_query);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$scores_result = $stmt->get_result();

if ($current_user_id) {
    // Kiểm tra xem user đã có điểm cho game này chưa
    $check_score_query = "
        SELECT score 
        FROM scores 
        WHERE user_id = ? AND game_id = ?
        ORDER BY score DESC 
        LIMIT 1
    ";
    $stmt = $conn->prepare($check_score_query);
    $stmt->bind_param("ii", $current_user_id, $game_id);
    $stmt->execute();
    $user_score_result = $stmt->get_result();
    
    if ($user_score_result->num_rows > 0) {
        // Nếu user đã có điểm, lấy xếp hạng hiện tại
        $current_user_rank_query = "
            SELECT 
                COUNT(*) + 1 as user_rank
            FROM 
                scores
            WHERE 
                game_id = ? 
                AND score > (
                    SELECT score 
                    FROM scores 
                    WHERE user_id = ? AND game_id = ?
                    ORDER BY score DESC 
                    LIMIT 1
                )
        ";
        $stmt = $conn->prepare($current_user_rank_query);
        $stmt->bind_param("iii", $game_id, $current_user_id, $game_id);
        $stmt->execute();
        $current_user_rank_result = $stmt->get_result();
        $current_user_rank = $current_user_rank_result->fetch_assoc();
    } else {
        // Nếu user chưa có điểm, set rank = 0
        $current_user_rank = ['user_rank' => 0];
    }

    // Lấy tổng số người chơi có điểm
    $total_players_query = "
        SELECT COUNT(DISTINCT user_id) as total
        FROM scores
        WHERE game_id = ?
    ";
    $stmt = $conn->prepare($total_players_query);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $total_players_result = $stmt->get_result();
    $total_players = $total_players_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Bảng Xếp Hạng</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .leaderboard-container {
        background: linear-gradient(135deg, #f6f8fc, #e9ecef);
        padding: 40px 20px;
        min-height: 100vh;
    }

    .leaderboard-header {
        text-align: center;
        padding: 20px;
        background: white;
        border-radius: 20px 20px 0 0;
    }

    .leaderboard-title {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(45deg, #FFD700, #FFA500);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 20px;
    }

    .leaderboard-select {
        padding: 15px 30px;
        font-size: 16px;
        border: 2px solid #eee;
        border-radius: 15px;
        width: 300px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .leaderboard-select:hover {
        border-color: #FFD700;
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.2);
    }

    .leaderboard-table-container {
        background: white;
        padding: 20px;
        overflow: hidden;
    }

    .leaderboard-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        text-align: center;
    }

    .leaderboard-table th {
        padding: 20px;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
        background: #f8f9fa;
    }

    .leaderboard-table tr {
        transition: transform 0.3s ease;
    }

    .leaderboard-table tr:hover {
        transform: translateY(-2px);
    }

    .leaderboard-table td {
        padding: 20px;
        background: #fff;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }

    .leaderboard-table tr.current-user {
        background: rgba(46, 213, 115, 0.1);
    }

    .medal {
        font-size: 24px;
        margin-right: 10px;
    }

    .gold {
        color: #FFD700;
    }

    .silver {
        color: #C0C0C0;
    }

    .bronze {
        color: #CD7F32;
    }

    .user-rank {
        background: rgb(255, 255, 255);
        padding: 20px;
        border-radius: 0 0 20px 20px;
        text-align: center;
        font-size: 1.1rem;
        color: #495057;
        border-top: 1px solid rgb(252, 115, 3);
    }

    .user-rank strong {
        color: #2ecc71;
        font-weight: 600;
    }

    .no-rank-message {
        background: #fff3cd;
        color: #856404;
        padding: 15px 20px;
        border-radius: 10px;
        border: 1px solid #ffeeba;
        font-size: 1rem;
        text-align: center;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .leaderboard-title {
            font-size: 2rem;
        }

        .leaderboard-select {
            width: 100%;
            max-width: 300px;
        }

        .leaderboard-table td,
        .leaderboard-table th {
            padding: 15px 10px;
            font-size: 14px;
        }
    }
    </style>
</head>

<body>
    <div class="leaderboard-container">
        <div class="leaderboard-header">
            <h1 class="leaderboard-title">Bảng Xếp Hạng</h1>
            <select class="leaderboard-select" onchange="window.location.href='?game_id='+this.value">
                <?php foreach ($games as $game): ?>
                <option value="<?php echo $game['id']; ?>" <?php echo $game_id == $game['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($game['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="leaderboard-table-container">
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Hạng</th>
                        <th>Người chơi</th>
                        <th>Điểm</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    while ($row = $scores_result->fetch_assoc()):
                        $is_current_user = ($current_user_id && $row['user_id'] == $current_user_id);
                    ?>
                    <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>">
                        <td>
                            <?php
                            if ($rank == 1) {
                                echo '<span class="medal gold"><i class="fas fa-medal"></i></span>';
                            } elseif ($rank == 2) {
                                echo '<span class="medal silver"><i class="fas fa-medal"></i></span>';
                            } elseif ($rank == 3) {
                                echo '<span class="medal bronze"><i class="fas fa-medal"></i></span>';
                            } else {
                                echo $rank;
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo number_format($row['score']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php 
                    $rank++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
        </div>

        <?php if ($current_user_id): ?>
        <div class="user-rank">
            <?php if ($current_user_rank['user_rank'] > 0): ?>
            Xếp hạng của bạn: <strong>#<?php echo $current_user_rank['user_rank']; ?></strong>
            trên tổng số <strong><?php echo $total_players['total']; ?></strong> người chơi
            <?php else: ?>
            <div class="no-rank-message">
                Bạn chưa có điểm số cho game này. Hãy chơi game để có thứ hạng nhé!
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>