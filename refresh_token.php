<?php
include_once 'config.php';

if (!function_exists('refreshAccessToken')) {
    function refreshAccessToken()
    {
        if (!isset($_COOKIE['refresh_token'])) {
            return false;
        }

        $refresh_token = $_COOKIE['refresh_token'];
        $payload = verifyJWT($refresh_token);

        if (!$payload || $payload['type'] !== 'refresh') {
            return false;
        }

        $user_id = $payload['user_id'];
        $username = $payload['username'];

        // Generate new access token
        $new_access_token = generateJWT([
            'user_id' => $user_id,
            'username' => $username,
            'type' => 'access'
        ], JWT_ACCESS_TOKEN_EXPIRY);

        // Update access token in database
        if (updateAccessToken($user_id, $new_access_token)) {
            // Set new access token cookie
            setcookie('access_token', $new_access_token, time() + JWT_ACCESS_TOKEN_EXPIRY, '/', '', true, true);
            return true;
        }

        return false;
    }
}

// Check if access token is expired and refresh token exists
if (isset($_COOKIE['access_token'])) {
    $access_token = $_COOKIE['access_token'];
    $payload = verifyJWT($access_token);

    if (!$payload) {
        // Access token is expired, try to refresh
        if (refreshAccessToken()) {
            // Token refreshed successfully
            return;
        } else {
            // Refresh failed, redirect to login
            header("Location: login.php");
            exit;
        }
    }
}
?>