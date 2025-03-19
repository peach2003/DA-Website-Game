<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php?success=1");
        exit;
    } else {
        echo "Lỗi: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Game Portal</title>
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

    .register-container {
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

    .register-header {
        text-align: center;
        margin-bottom: 2rem;
        color: white;
    }

    .register-header h1 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(45deg, #00ff87, #60efff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .register-header p {
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

    .register-btn {
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
        margin-top: 1rem;
    }

    .register-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 255, 135, 0.3);
    }

    .register-btn::before {
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

    .register-btn:hover::before {
        left: 100%;
    }

    .login-link {
        text-align: center;
        margin-top: 1.5rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .login-link a {
        color: #00ff87;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .login-link a:hover {
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

    .password-requirements {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 0.5rem;
        padding-left: 20px;
    }

    @media (max-width: 480px) {
        .register-container {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Đăng ký</h1>
            <p>Tạo tài khoản mới để chơi game</p>
        </div>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder=" " required>
                <label>Tên đăng nhập</label>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder=" " required>
                <label>Email</label>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder=" " required>
                <label>Mật khẩu</label>
                <div class="password-requirements">
                    Mật khẩu phải có ít nhất 6 ký tự
                </div>
            </div>
            <button type="submit" class="register-btn">Đăng ký</button>
        </form>
        <div class="login-link">
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
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