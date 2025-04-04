<?php
if (!isset($_COOKIE['refresh_token'])) {
    return;
}

$refresh_token = $_COOKIE['refresh_token'];
$payload = verifyJWT($refresh_token);

// Kiểm tra refresh token có hợp lệ không
if ($payload && $payload['type'] === 'refresh') {
    $user_id = $payload['user_id'];
    
    // Kiểm tra token trong database
    $stored_tokens = getValidTokens($user_id);
    
    if ($stored_tokens && $stored_tokens['refresh_token'] === $refresh_token) {
        // Tạo access token mới
        $new_access_payload = [
            'user_id' => $user_id,
            'type' => 'access'
        ];
        
        $new_access_token = generateJWT($new_access_payload, JWT_ACCESS_TOKEN_EXPIRY);
        
        // Cập nhật trong database
        updateAccessToken($user_id, $new_access_token);
        
        // Cập nhật cookie
        setcookie('access_token', $new_access_token, time() + JWT_ACCESS_TOKEN_EXPIRY, '/', '', true, true);
        
        // Nếu refresh token sắp hết hạn (còn 1 ngày), tạo mới
        if ($payload['exp'] - time() < 24 * 60 * 60) {
            $new_refresh_payload = [
                'user_id' => $user_id,
                'type' => 'refresh'
            ];
            
            $new_refresh_token = generateJWT($new_refresh_payload, JWT_REFRESH_TOKEN_EXPIRY);
            
            // Cập nhật refresh token trong database
            $stmt = $conn->prepare("UPDATE user_tokens SET refresh_token = ?, expires_at = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE user_id = ?");
            $stmt->bind_param("si", $new_refresh_token, $user_id);
            $stmt->execute();
            
            // Cập nhật cookie refresh token
            setcookie('refresh_token', $new_refresh_token, time() + JWT_REFRESH_TOKEN_EXPIRY, '/', '', true, true);
        }
    }
}
?>