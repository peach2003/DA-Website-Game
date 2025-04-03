<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    // Delete tokens from database
    deleteTokens($_SESSION['user_id']);

    // Delete cookies
    setcookie('access_token', '', time() - 3600, '/', '', true, true);
    setcookie('refresh_token', '', time() - 3600, '/', '', true, true);
}

session_destroy();
header("Location: login.php");
exit;
?>