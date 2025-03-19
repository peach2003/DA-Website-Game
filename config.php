<?php
$host = "localhost";
$user = "root"; // XAMPP mặc định là "root"
$pass = ""; // Không có mật khẩu
$db = "game_portal";

// Kết nối MySQL
$conn = new mysqli($host, $user, $pass, $db);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
