<?php
session_start();
require_once 'config.php';
include 'refresh_token.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Bạn cần đăng nhập để lưu điểm']);
    exit;
}

// Kiểm tra dữ liệu gửi lên
if (!isset($_POST['game_id']) || !isset($_POST['score'])) {
    echo json_encode(['error' => 'Thiếu thông tin điểm hoặc game ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = intval($_POST['game_id']);
$score = intval($_POST['score']);

// Kiểm tra game có tồn tại
$stmt = $conn->prepare("SELECT id FROM games WHERE id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Game không tồn tại']);
    exit;
}

// Kiểm tra điểm số cũ của user cho game này
$stmt = $conn->prepare("SELECT score FROM scores WHERE user_id = ? AND game_id = ? ORDER BY score DESC LIMIT 1");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Đã có điểm số cũ
    $old_score = $result->fetch_assoc()['score'];

    if ($score <= $old_score) {
        // Điểm mới không cao hơn điểm cũ
        echo json_encode([
            'success' => true,
            'message' => 'Điểm số ' . $score . ' thấp hơn điểm trong bảng xếp hạng của bạn (' . $old_score . '). Hãy cố gắng phá kỷ lục nhé!'
        ]);
        exit;
    }

    // Xóa điểm cũ và thêm điểm mới
    $stmt = $conn->prepare("DELETE FROM scores WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
}

// Lưu điểm mới
$stmt = $conn->prepare("INSERT INTO scores (user_id, game_id, score) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $user_id, $game_id, $score);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => isset($old_score) ?
            'Chúc mừng! Bạn đã phá kỷ lục với điểm số mới: ' . $score . ' (điểm cũ: ' . $old_score . ')' :
            'Đã lưu điểm thành công: ' . $score . '. Đây là lần đầu tiên bạn chơi game này!'
    ]);
} else {
    echo json_encode(['error' => 'Lỗi khi lưu điểm: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>