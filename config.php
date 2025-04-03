<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'game_portal';

// JWT configuration
if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', 'your-secret-key-here'); // Thay đổi thành key bí mật của bạn
}
if (!defined('JWT_ACCESS_TOKEN_EXPIRY')) {
    define('JWT_ACCESS_TOKEN_EXPIRY', 3 * 60 * 60); // 3 hours in seconds
}
if (!defined('JWT_REFRESH_TOKEN_EXPIRY')) {
    define('JWT_REFRESH_TOKEN_EXPIRY', 7 * 24 * 60 * 60); // 7 days in seconds
}

// Database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// JWT Helper Functions
if (!function_exists('generateJWT')) {
    function generateJWT($payload, $expiry)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header = base64url_encode($header);

        $payload['exp'] = time() + $expiry;
        $payload = json_encode($payload);
        $payload = base64url_encode($payload);

        $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET_KEY, true);
        $signature = base64url_encode($signature);

        return "$header.$payload.$signature";
    }
}

if (!function_exists('verifyJWT')) {
    function verifyJWT($token)
    {
        list($header, $payload, $signature) = explode('.', $token);

        $valid_signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET_KEY, true);
        $valid_signature = base64url_encode($valid_signature);

        if ($signature !== $valid_signature) {
            return false;
        }

        $payload = json_decode(base64url_decode($payload), true);

        if ($payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}

// Token Management Functions
if (!function_exists('saveTokens')) {
    function saveTokens($user_id, $access_token, $refresh_token)
    {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, access_token, refresh_token, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $access_token, $refresh_token);
        return $stmt->execute();
    }
}

if (!function_exists('updateAccessToken')) {
    function updateAccessToken($user_id, $new_access_token)
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE user_tokens SET access_token = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_access_token, $user_id);
        return $stmt->execute();
    }
}

if (!function_exists('deleteTokens')) {
    function deleteTokens($user_id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
}

if (!function_exists('getValidTokens')) {
    function getValidTokens($user_id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT access_token, refresh_token FROM user_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
}
?>