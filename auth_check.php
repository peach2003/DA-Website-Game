<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';

$is_logged_in = false;
$auth = null;

if (isset($_COOKIE['access_token'])) {
    include_once 'refresh_token.php';

    $access_token = $_COOKIE['access_token'];
    $payload = verifyJWT($access_token);

    if ($payload && $payload['type'] === 'access') {
        $is_logged_in = true;
        $auth = $payload;
        // Kiểm tra và gia hạn token nếu cần
        if (isset($payload['user_id'])) {
            $renewal_result = checkAndRenewToken($payload['user_id']);
            if ($renewal_result['renewed']) {
                // Có thể log hoặc thông báo cho user biết token đã được gia hạn
                error_log("Token của user {$payload['user_id']} đã được gia hạn tự động");
            }
        }
    }
}

if (!function_exists('requireAuth')) {
    function requireAuth() {
        global $is_logged_in;

        if (!$is_logged_in) {
            header("Location: login.php");
            exit;
        }
    }
}

if (!function_exists('optionalAuth')) {
    function optionalAuth() {
        global $is_logged_in, $auth;
        return $is_logged_in ? $auth : null;
    }
}
?>