<?php
// Chỉ khởi tạo session nếu chưa có session nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';
include_once 'refresh_token.php';
include_once 'auth_check.php';

// Lấy thông tin người dùng đã đăng nhập (nếu có)
$user = optionalAuth();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT id, username, avatar, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $is_logged_in = true;
} else {
    $is_logged_in = false;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Roboto', sans-serif;
    }

    body {
        background: #ffffff;
        /* Nền trắng theo yêu cầu */
        color: #2c3e50;
        padding-top: 90px;
        /* Thêm padding-top để nội dung không bị che bởi header cố định */
    }

    /* Header */
    header {
        background: rgba(255, 255, 255, 0.95);
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        backdrop-filter: blur(10px);
        /* Hiệu ứng kính mờ */
    }

    header .container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .logo {
        display: flex;
        align-items: center;
        padding: 5px;
    }

    .logo img {
        width: 70px;
        height: 70px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .logo:hover img {
        transform: scale(1.1) rotate(5deg);
        /* Hiệu ứng xoay nhẹ khi hover */
    }

    .nav-buttons {
        display: flex;
        gap: 10px;
    }

    .nav-btn {
        padding: 8px 16px;
        border: none;
        background: none;
        cursor: pointer;
        font-weight: 500;
        color: #666;
        transition: color 0.3s ease;
    }

    .nav-btn:hover {
        color: #e74c3c;
        /* Màu đỏ nổi bật khi hover */
    }

    .nav-btn.active {
        color: #e74c3c;
        position: relative;
    }

    .nav-btn.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 20px;
        height: 2px;
        background: #e74c3c;
        border-radius: 1px;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.2);
        /* Hiệu ứng kính mờ */
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 25px;
        padding: 8px 20px;
        width: 450px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }

    .search-box:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background: rgba(255, 255, 255, 0.3);
    }

    .search-box input {
        flex: 1;
        border: none;
        background: none;
        padding: 8px;
        outline: none;
        font-size: 15px;
        color: #34495e;
    }

    .search-box button {
        border: none;
        background: none;
        cursor: pointer;
        color: #e74c3c;
        padding: 0 5px;
        transition: transform 0.3s ease;
    }

    .search-box button:hover {
        transform: scale(1.1);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .dropdown {
        position: relative;
    }

    .dropdown-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        background: #fff;
        border: none;
        cursor: pointer;
        text-align: center;
        margin: auto;
        padding: 15px 25px;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .dropdown-btn:hover {
        background: rgb(245, 52, 30);
        transform: translateY(-2px);
        color: white;
    }

    .btn-login {
        text-decoration: none;
        padding: 15px 20px;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 2px solid #e74c3c;
        color: #e74c3c;
    }

    .btn-login:hover {
        text-decoration: none;
        background: #e74c3c;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .header-left {
            flex-direction: column;
            gap: 15px;
        }

        .search-box {
            width: 100%;
        }

        .header-right {
            gap: 10px;
        }

        .btn-login {
            padding: 8px 15px;
            font-size: 14px;
        }

        .btn-leaderboard {
            padding: 6px 15px;
            font-size: 14px;
        }

        .btn-leaderboard i {
            font-size: 14px;
        }
    }

    .dropdown-item {
        padding: 8px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #333;
        font-size: 14px;
        text-decoration: none;
        transition: background-color 0.2s ease;
        cursor: pointer;
    }

    .dropdown-item:hover {
        background-color: #f5f5f5;
    }

    .dropdown-item i {
        font-size: 14px;
        width: 16px;
        text-align: center;
        color: #666;
    }

    /* Đảm bảo z-index cho dropdown */
    .user-dropdown {
        position: absolute;
        top: calc(100% + 5px);
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        padding: 6px 0;
        margin-top: 5px;
        display: none;
        z-index: 1000;
    }

    .user-dropdown.active {
        display: block;
        animation: dropdownFadeIn 0.2s ease-out;
    }

    /* Animation cho dropdown */
    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 8px;
        border-radius: 20px;
        transition: background-color 0.3s ease;
        cursor: pointer;
    }

    .user-info:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .leaderboard-btn {
        margin: 0 10px;
    }



    .btn-leaderboard {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: row;
        background: #fff;
        border: none;
        cursor: pointer;
        text-align: center;
        margin: auto;
        padding: 15px 25px;
        border-radius: 8px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: rgb(0, 0, 0);
        gap: 10px;

    }

    .btn-leaderboard:hover,
    .btn-leaderboard i:hover {
        background: rgb(245, 52, 30);
        transform: translateY(-2px);
        color: white;
        text-decoration: none !important
    }

    .btn-leaderboard i {
        font-size: 15px;
        color: rgb(246, 253, 49);
    }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <img src="./assets/image_web/logo.png" alt="Logo">
                </a>
                <div class="search-box">
                    <input type="text" placeholder="Tìm kiếm 90 000 game của chúng tôi">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <!-- Thay thế phần header-right trong header.php -->
            <div class="header-right">
                <div class="leaderboard-btn">
                    <a href="leaderboard.php" class="btn-leaderboard">
                        <i class="fas fa-trophy"></i>
                        Bảng xếp hạng
                    </a>
                </div>
                <div class="dropdown">
                    <button class="dropdown-btn">
                        Thể loại </button>
                </div>
                <?php if ($is_logged_in): ?>
                <div class="user-menu" id="userMenu">
                    <div class="user-info">
                        <img src="<?php
                            if (!empty($user['avatar'])) {
                                $avatar_path = './assets/image_avatars/' . $user['avatar'];
                                if (file_exists($avatar_path)) {
                                    echo $avatar_path;
                                } else {
                                    echo './assets/image_avatars/default-avatar.png';
                                }
                            } else {
                                echo './assets/image_avatars/default-avatar.png';
                            }
                            ?>" alt="Avatar của <?php echo htmlspecialchars($user['username']); ?>"
                            class="user-avatar">
                        <span class="user-name"><?php echo htmlspecialchars($user['username']); ?></span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Tài khoản
                        </a>
                        <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            Quản trị viên
                        </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Đăng xuất
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn-login">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const userMenu = document.querySelector('.user-menu');
        const userDropdown = document.querySelector('.user-dropdown');
        const dropdownArrow = document.querySelector('.dropdown-arrow');
        const userInfo = document.querySelector('.user-info'); // Thêm selector cho phần user-info

        if (userMenu && userDropdown && dropdownArrow && userInfo) {
            // Xử lý sự kiện click vào user-info (avatar và tên)
            userInfo.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Toggle class active cho dropdown
                userDropdown.classList.toggle('active');

                // Xoay mũi tên
                dropdownArrow.style.transform = userDropdown.classList.contains('active') ?
                    'rotate(180deg)' :
                    'rotate(0)';
            });

            // Cho phép click vào các item trong dropdown
            userDropdown.addEventListener('click', function(e) {
                // Nếu click vào link trong dropdown, cho phép chuyển hướng bình thường
                if (e.target.closest('.dropdown-item')) {
                    return true;
                }
                e.stopPropagation();
            });

            // Đóng dropdown khi click ra ngoài
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target)) {
                    userDropdown.classList.remove('active');
                    dropdownArrow.style.transform = 'rotate(0)';
                }
            });
        }
    });
    </script>
</body>

</html>