<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Bạn cần đăng nhập để lưu điểm']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['game_id']) || !isset($data['score'])) {
    echo json_encode(['error' => 'Thiếu thông tin điểm hoặc game ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = intval($data['game_id']);
$score = intval($data['score']);

$stmt = $conn->prepare("INSERT INTO scores (user_id, game_id, score) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $user_id, $game_id, $score);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật điểm thành công']);
} else {
    echo json_encode(['error' => 'Lỗi khi cập nhật điểm']);
}

$stmt->close();
$conn->close();
?>