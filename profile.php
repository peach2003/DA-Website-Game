<?php
include 'auth_check.php';

// Yêu cầu đăng nhập để truy cập trang này
requireAuth();

// Lấy thông tin người dùng
$user_id = $auth['user_id'];
$username = $auth['username'];

// Lấy thông tin chi tiết từ database
$stmt = $conn->prepare("SELECT email, avatar, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ người dùng - Game Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <div class="container">
            <h1>Game Portal</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="games.php">Danh sách game</a></li>
                    <li><a href="profile.php">Tài khoản</a></li>
                    <li><a href="logout.php">Đăng xuất</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="profile-section">
            <h2>Hồ sơ người dùng</h2>
            <div class="profile-info">
                <div class="avatar">
                    <img src="images/avatars/<?php echo htmlspecialchars($user_data['avatar']); ?>" alt="Avatar">
                </div>
                <div class="user-details">
                    <p><strong>Tên người dùng:</strong> <?php echo htmlspecialchars($username); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                    <p><strong>Vai trò:</strong> <?php echo htmlspecialchars($user_data['role']); ?></p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Game Portal. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>