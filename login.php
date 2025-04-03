<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Generate tokens
            $access_token = generateJWT([
                'user_id' => $id,
                'username' => $username,
                'type' => 'access'
            ], JWT_ACCESS_TOKEN_EXPIRY);

            $refresh_token = generateJWT([
                'user_id' => $id,
                'username' => $username,
                'type' => 'refresh'
            ], JWT_REFRESH_TOKEN_EXPIRY);

            // Save tokens to database
            saveTokens($id, $access_token, $refresh_token);

            // Set cookies
            setcookie('access_token', $access_token, time() + JWT_ACCESS_TOKEN_EXPIRY, '/', '', true, true);
            setcookie('refresh_token', $refresh_token, time() + JWT_REFRESH_TOKEN_EXPIRY, '/', '', true, true);

            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            echo "<script>alert('Đăng nhập thành công!'); window.location.href='index.php';</script>";
            exit;
        } else {
            echo "<script>alert('Sai mật khẩu!'); window.location.href='login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Email không tồn tại!'); window.location.href='login.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a1a, #4a4a4a);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
        }

        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #00ff87, #60efff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00ff87;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 15px rgba(0, 255, 135, 0.2);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group label {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-group input:focus+label,
        .form-group input:not(:placeholder-shown)+label {
            top: -10px;
            left: 10px;
            font-size: 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0 5px;
            color: #00ff87;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #00ff87, #60efff);
            border: none;
            border-radius: 10px;
            color: #1a1a1a;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 135, 0.3);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, 0.3),
                    transparent);
            transition: 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .register-link a {
            color: #00ff87;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: #60efff;
            text-shadow: 0 0 10px rgba(0, 255, 135, 0.5);
        }

        .game-icons {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            z-index: 1;
        }

        .game-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .game-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Game Portal</h1>
            <p>Đăng nhập để chơi game</p>
        </div>
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder=" " required>
                <label>Email</label>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder=" " required>
                <label>Mật khẩu</label>
            </div>
            <button type="submit" class="login-btn">Đăng nhập</button>
        </form>
        <div class="register-link">
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </div>
    </div>

    <div class="game-icons">
        <div class="game-icon">🎮</div>
        <div class="game-icon">🎲</div>
        <div class="game-icon">🎯</div>
        <div class="game-icon">🎨</div>
    </div>
</body>

</html>