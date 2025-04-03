<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    .footer {
        background-color: #fff;
        color: #333;
        padding: 40px 20px;
        font-family: 'Arial', sans-serif;
        border-top: 1px solid #ddd;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .footer-left {
        width: auto;
        margin-bottom: 10px;
    }

    .footer-logo-img {
        width: 100px;
        margin-bottom: 10px;
    }

    .footer-left p {
        color: #999;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0;
        line-height: 16px;
    }

    .footer-left a:hover {
        color: #ff6347;
    }

    /* Middle section */
    .footer-middle {
        width: auto;
        margin-bottom: 20px;
    }

    .footer-middle h3 {
        color: #333;
        display: block;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 0;
        margin-bottom: 7px;
        text-transform: uppercase;
    }

    .footer-middle ul {
        list-style: none;
        padding: 0;
    }

    .footer-middle li {
        margin: 10px 0;
    }

    .footer-middle a {
        color: #999;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0;
        line-height: 16px;
        text-decoration: none;
    }

    /* Right section */
    .footer-right {
        width: 30%;
        margin-bottom: 20px;
    }

    .footer-right h3 {
        color: #333;
        display: block;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 0;
        margin-bottom: 7px;
        text-transform: uppercase;
    }

    .footer-right ul {
        list-style: none;
        padding: 0;
    }

    .footer-right li {
        margin: 10px 0;
    }

    .footer-right a {
        color: #999;
        font-size: 13px;
        font-weight: 500;
        letter-spacing: 0;
        line-height: 16px;
        text-decoration: none;
    }

    /* Follow us section */
    .footer-follow {
        width: auto;
        text-align: center;
        margin-bottom: 20px;
    }

    .footer-follow h3 {
        color: #333;
        display: block;
        font-size: 13px;
        font-weight: bold;
        letter-spacing: 0;
        margin-bottom: 7px;
        text-transform: uppercase;
    }

    .social-icons {
        display: flex;
        flex-direction: column;
    }

    .social-icon {
        font-size: 20px;
        margin: 0 15px;
        color: rgb(19, 165, 244);
        text-decoration: none;
    }

    .fa,
    .fa-brands,
    .fa-duotone,
    .fa-light,
    .fa-regular,
    .fa-solid,
    .fa-thin,
    .fab,
    .fad,
    .fal,
    .far,
    .fas,
    .fat {
        -moz-osx-font-smoothing: grayscale;
        -webkit-font-smoothing: antialiased;
        display: var(--fa-display, inline-block);
        font-style: normal;
        font-variant: normal;
        line-height: 2;
        text-rendering: auto;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            align-items: center;
        }

        .footer-left,
        .footer-middle,
        .footer-right,
        .footer-follow {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }
    }
    </style>
</head>

<body>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">
                    <img src="./assets/image_web/logo.png" alt="Logo" class="footer-logo-img">
                </div>
            </div>

            <div class="footer-middle">
                <h3>Trò Chơi Trực Tuyến Miễn Phí Tại Y8</h3>
                <ul>
                    <li><a href="#">Game Miễn Phí Trực Tuyến Mới</a></li>
                    <li><a href="#">Game Miễn Phí Trực Tuyến Hay Nhất</a></li>
                    <li><a href="#">Game Miễn Phí Trực Tuyến Phổ Biến</a></li>
                    <li><a href="#">Trình Duyệt Y8 (để chơi Game Flash)</a></li>
                    <li><a href="#">Các Studio Game Trình Duyệt</a></li>
                    <li><a href="#">Tải Lên</a></li>
                </ul>
            </div>

            <div class="footer-right">
                <h3>Công Ty</h3>
                <ul>
                    <li><a href="#">Điều Khoản Sử Dụng</a></li>
                    <li><a href="#">Chính Sách Bảo Mật</a></li>
                    <li><a href="#">Chính Sách Cookie</a></li>
                    <li><a href="#">Nhà Phát Hành Game</a></li>
                    <li><a href="#">Các Nhà Phát Triển Game</a></li>
                    <li><a href="#">Gửi Tin Nhắn Cho Chúng Tôi</a></li>
                    <li><a href="#">Gửi Mail Cho Chúng Tôi</a></li>
                </ul>
            </div>

            <div class="footer-follow">
                <h3>Theo Dõi Chúng Tôi</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>