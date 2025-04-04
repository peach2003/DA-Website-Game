<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'game_portal';

// JWT configuration
if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', 'your-secret-key-here');
}
if (!defined('JWT_ACCESS_TOKEN_EXPIRY')) {
    define('JWT_ACCESS_TOKEN_EXPIRY', 3 * 60 * 60); // 3 giờ
}
if (!defined('JWT_REFRESH_TOKEN_EXPIRY')) {
    define('JWT_REFRESH_TOKEN_EXPIRY', 7 * 24 * 60 * 60); // 7 ngày
}

// Database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// JWT Helper Functions
if (!function_exists('generateJWT')) {
    function generateJWT($payload, $expiry) {
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
    function verifyJWT($token) {
        list($header, $payload, $signature) = explode('.', $token);

        $valid_signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET_KEY, true);
        $valid_signature = base64url_encode($valid_signature);

        if ($signature !== $valid_signature) {
            return false;
        }

        $payload = json_decode(base64url_decode($payload), true);

        if ($payload['exp'] < time()) {
            if (isset($payload['user_id'])) {
                cleanExpiredTokens();
            }
            return false;
        }

        return $payload;
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}

// Token Management Functions
if (!function_exists('saveTokens')) {
    function saveTokens($user_id, $access_token, $refresh_token) {
        global $conn;
        
        // Tính thời gian hết hạn
        $expires_at = date('Y-m-d H:i:s', time() + JWT_REFRESH_TOKEN_EXPIRY);
        
        // Xóa token cũ của user
        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Lưu token mới
        $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, access_token, refresh_token, created_at, expires_at) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->bind_param("isss", $user_id, $access_token, $refresh_token, $expires_at);
        
        // Xóa token hết hạn
        cleanExpiredTokens();
        
        return $stmt->execute();
    }
}

if (!function_exists('updateAccessToken')) {
    function updateAccessToken($user_id, $new_access_token) {
        global $conn;
        
        $stmt = $conn->prepare("UPDATE user_tokens SET access_token = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_access_token, $user_id);
        return $stmt->execute();
    }
}

if (!function_exists('cleanExpiredTokens')) {
    function cleanExpiredTokens() {
        global $conn;
        
        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE expires_at < NOW()");
        return $stmt->execute();
    }
}

if (!function_exists('getValidTokens')) {
    function getValidTokens($user_id) {
        global $conn;
        
        $stmt = $conn->prepare("SELECT access_token, refresh_token FROM user_tokens WHERE user_id = ? AND expires_at > NOW()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

// Thêm hàm mới để kiểm tra và gia hạn token
function checkAndRenewToken($user_id) {
    global $conn;
    
    try {
        // Lấy thông tin token hiện tại
        $stmt = $conn->prepare("
            SELECT access_token, refresh_token, expires_at 
            FROM user_tokens 
            WHERE user_id = ? AND expires_at > NOW()
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $token = $result->fetch_assoc();
            
            // Tính thời gian còn lại đến khi hết hạn (theo giây)
            $time_to_expire = strtotime($token['expires_at']) - time();
            $one_day_in_seconds = 24 * 60 * 60;
            
            // Nếu còn dưới 1 ngày -> tạo token mới
            if ($time_to_expire < $one_day_in_seconds) {
                // Tạo payload cho token mới
                $access_payload = [
                    'user_id' => $user_id,
                    'type' => 'access'
                ];
                
                $refresh_payload = [
                    'user_id' => $user_id,
                    'type' => 'refresh'
                ];
                
                // Tạo token mới
                $new_access_token = generateJWT($access_payload, JWT_ACCESS_TOKEN_EXPIRY);
                $new_refresh_token = generateJWT($refresh_payload, JWT_REFRESH_TOKEN_EXPIRY);
                
                // Lưu token mới vào database
                saveTokens($user_id, $new_access_token, $new_refresh_token);
                
                // Cập nhật cookie với token mới
                setcookie('access_token', $new_access_token, time() + JWT_ACCESS_TOKEN_EXPIRY, '/', '', true, true);
                setcookie('refresh_token', $new_refresh_token, time() + JWT_REFRESH_TOKEN_EXPIRY, '/', '', true, true);
                
                return [
                    'renewed' => true,
                    'message' => 'Token đã được gia hạn tự động'
                ];
            }
        }
        
        return [
            'renewed' => false,
            'message' => 'Token vẫn còn hạn sử dụng'
        ];
        
    } catch (Exception $e) {
        error_log("Lỗi khi gia hạn token: " . $e->getMessage());
        return [
            'renewed' => false,
            'message' => 'Có lỗi xảy ra khi gia hạn token'
        ];
    }
}
// Hàm xóa token khi đăng xuất
function deleteTokens($user_id) {
    global $conn;
    
    try {
        // Chuẩn bị câu lệnh SQL để xóa tất cả token của user
        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        
        // Thực thi câu lệnh
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Lỗi khi xóa token: " . $stmt->error);
            return false;
        }
    } catch (Exception $e) {
        error_log("Exception khi xóa token: " . $e->getMessage());
        return false;
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}
?>