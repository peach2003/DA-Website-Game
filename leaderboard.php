<?php
session_start();
include 'config.php';
include 'refresh_token.php';
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : null;

// Lấy danh sách game để hiển thị dropdown
$games_result = $conn->query("SELECT id, title FROM games");
$games = [];
while ($row = $games_result->fetch_assoc()) {
    $games[] = $row;
}

// Lấy điểm cao nhất của mỗi người chơi cho game đã chọn
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
    <title>Bảng Xếp Hạng</title>
    <style>
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
        background-color: #f5f5f5;
    }

    select {
        padding: 5px;
        margin: 10px 0;
    }
    </style>
</head>

<body>
    <h1>Bảng Xếp Hạng</h1>

    <form method="GET">
        <select name="game_id" onchange="this.form.submit()">
            <option value="">Chọn game</option>
            <?php foreach ($games as $game): ?>
            <option value="<?php echo $game['id']; ?>" <?php echo $game_id == $game['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($game['title']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($game_id): ?>
    <table>
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
    <?php endif; ?>

    <p><a href="index.php">Quay lại trang chủ</a></p>
</body>

</html>