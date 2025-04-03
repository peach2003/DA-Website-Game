<?php
// Chỉ khởi tạo session nếu chưa có session nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';

// Biến để kiểm tra xem người dùng đã đăng nhập hay chưa
$is_logged_in = false;
$auth = null;

// Kiểm tra xem có access_token không
if (isset($_COOKIE['access_token'])) {
    include_once 'refresh_token.php';

    $access_token = $_COOKIE['access_token'];
    $payload = verifyJWT($access_token);

    if ($payload && $payload['type'] === 'access') {
        $is_logged_in = true;
        $auth = $payload;
    }
}

// Hàm kiểm tra xác thực bắt buộc
if (!function_exists('requireAuth')) {
    function requireAuth()
    {
        global $is_logged_in;

        if (!$is_logged_in) {
            header("Location: login.php");
            exit;
        }
    }
}

// Hàm kiểm tra xác thực tùy chọn
if (!function_exists('optionalAuth')) {
    function optionalAuth()
    {
        global $is_logged_in, $auth;
        return $is_logged_in ? $auth : null;
    }
}
?>