<?php
// Chỉ khởi tạo session nếu chưa có session nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';
include_once 'refresh_token.php';
include_once 'auth_check.php';

// Hàm tạo URL ảnh game dựa trên tên game
function getGameImage($title)
{
    $gameImages = [
        'pacman' => 'https://th.bing.com/th/id/R.5e2f2399be25799401513ea1fcbac5ba?rik=avMBew%2bAzW27Og&riu=http%3a%2f%2fcdn.cnn.com%2fcnnnext%2fdam%2fassets%2f200518114838-05-pac-man-40.jpg&ehk=rDP9NuHnVcOvu8xJW0I4ADgb%2fnsMQk8r5wA5eUNHZtA%3d&risl=&pid=ImgRaw&r=0',
        'flappybird' => 'https://danviet.mediacdn.vn/296231569849192448/2023/8/5/hanh-trinh-flappy-bird-nguyen-ha-dong-1691264866444132753304.jpeg',
        'snake' => 'https://play-lh.googleusercontent.com/v8w4fmZli_DKkn9tN5P_tr0Wvky4zVjSf0pmd9VATiRJV-yfpI9cDwnpAMmpMh3tz94',
        'breakout' => 'https://th.bing.com/th/id/OIP.UX9SuvFo0NsIKUgHAYxIuQHaFj?rs=1&pid=ImgDetMain'
    ];

    // Chuyển tên game thành chữ thường và bỏ khoảng trắng
    $cleanTitle = strtolower(str_replace(' ', '', $title));

    // Trả về URL ảnh tương ứng hoặc ảnh mặc định
    return isset($gameImages[$cleanTitle])
        ? $gameImages[$cleanTitle]
        : 'https://img.freepik.com/premium-vector/video-game-controller-neon-sign-night-bright-advertisement_99087-158.jpg';
}

// Lấy thông tin người dùng đã đăng nhập (nếu có)
$user = optionalAuth();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Roboto', sans-serif;
    }

    body {
        background: #f0f2f5;
        color: #1a1a1a;
    }

    header {
        background: #fff;
        padding: 10px 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    header .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .logo {
        display: flex;
        align-items: center;
        padding: 5px;
    }

    .logo img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        transition: transform 0.2s ease;
    }

    .logo:hover img {
        transform: scale(1.05);
    }

    .nav-buttons {
        display: flex;
        gap: 10px;
    }

    .nav-btn {
        padding: 8px 16px;
        border: none;
        background: none;
        cursor: pointer;
        font-weight: 500;
        color: #666;
    }

    .nav-btn.active {
        color: #ff0000;
    }

    .search-box {
        display: flex;
        align-items: center;
        background: #f5f5f5;
        border-radius: 20px;
        padding: 5px 15px;
        width: 400px;
    }

    .search-box input {
        flex: 1;
        border: none;
        background: none;
        padding: 8px;
        outline: none;
        font-size: 14px;
    }

    .search-box button {
        border: none;
        background: none;
        cursor: pointer;
        color: #666;
        padding: 0 5px;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .dropdown {
        position: relative;
    }

    .dropdown-btn {
        display: flex;
        flex-direction: column;
        background: none;
        border: none;
        cursor: pointer;
        text-align: left;
        padding: 5px 10px;
    }

    .subtitle {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }

    .btn-login,
    .btn-register {
        padding: 8px 20px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
    }

    .btn-register {
        background: #ff0000;
        color: white;
    }

    .btn-login {
        border: 1px solid #ddd;
        color: #333;
    }

    .language-btn {
        border: none;
        background: none;
        cursor: pointer;
        padding: 4px;
    }

    .language-btn img {
        width: 24px;
        height: 24px;
        border-radius: 2px;
    }

    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    section {
        margin-bottom: 3rem;
    }

    section h2 {
        font-size: 2rem;
        margin-bottom: 1.5rem;
        color: #1a1a1a;
        text-align: center;
    }

    .game-grid,
    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        padding: 1rem;
    }

    .game-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        cursor: pointer;
        text-decoration: none;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .game-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .game-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 0.8rem;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
        color: white;
        font-size: 1rem;
        text-align: center;
    }

    .game-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .game-card:hover .game-overlay {
        opacity: 1;
    }

    .play-text {
        color: white;
        background: #4CAF50;
        padding: 0.6rem 1.2rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.9rem;
        transform: translateY(20px);
        transition: transform 0.3s ease;
    }

    .game-card:hover .play-text {
        transform: translateY(0);
    }

    .category-card {
        background: white;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .category-card:hover {
        transform: translateY(-5px);
    }

    .category-card h3 {
        color: #1a1a1a;
        margin-bottom: 0.5rem;
    }

    .category-card p {
        color: #666;
        margin-bottom: 1rem;
    }

    .category-link {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #4CAF50;
        color: white;
        text-decoration: none;
        border-radius: 20px;
        transition: background-color 0.3s ease;
    }

    .category-link:hover {
        background: #388E3C;
    }

    @media (max-width: 768px) {

        .game-grid,
        .category-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }

        nav ul {
            gap: 1rem;
        }

        section h2 {
            font-size: 1.5rem;
        }

        .game-title {
            font-size: 1rem;
        }

    }


    /* CSS cho phân trang */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 2rem;
    }

    .page-link {
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
        background: #fff;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background: #f5f5f5;
        border-color: #999;
    }

    .page-link.active {
        background: #ff0000;
        color: white;
        border-color: #ff0000;
    }

    .page-link:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

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
    <header>
        <div class="container">
            <div class="header-left"><a href="index.php" class="logo"><img src="./assets/image_web/logo.png"
                        alt="Logo"></a>
                <div class="search-box"><input type="text" placeholder="Tìm kiếm 90 000 game của chúng tôi"><button
                        type="submit"><i class="fas fa-search"></i></button></div>
            </div>
            <div class="header-right">
                <div class="dropdown"><button class="dropdown-btn">Thể loại <span class="subtitle">nhiều hơn</span>
                    </button>
                </div><?php if ($is_logged_in): ?> <a href="profile.php" class="user-profile">Tài khoản</a> <a
                    href="logout.php" class="btn-logout">Đăng xuất</a> <?php else: ?> <a href="login.php"
                    class="btn-login">Đăng nhập</a> <a href="register.php" class="btn-register">Đăng ký</a> <?php endif;
                ?>
            </div>
        </div>
    </header>
    <div class="filter-section" style="margin-top:20px; padding: 20px; background: #fff;">

        <div class="categories-row">
            <div class="filter-container" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;"><?php $categories_query = "SELECT * FROM categories";
            $categories_result = $conn->query($categories_query);
            $all_categories = [];

            while ($category = $categories_result->fetch_assoc()) {
                $all_categories[] = $category;
            }

            // Hiển thị 5 categories đầu tiên
            for ($i = 0; $i < min(5, count($all_categories)); $i++):
                $category = $all_categories[$i];
                ?>
                <div class="category-item filter-item"
                    style="padding: 5px 15px; background: #e0e0e0; border-radius: 20px; font-size: 14px; cursor: pointer;"><?php echo htmlspecialchars($category['name']);
                        ?></div><?php endfor;
            ?><?php if (count($all_categories) > 5): ?><button id="showAllCategoriesBtn" onclick="toggleCategories()"
                    class="show-more-btn"
                    style="padding: 5px 15px; background: #4CAF50; color: white; border: none; border-radius: 20px; font-size: 14px; cursor: pointer; white-space: nowrap;">Tất
                    cả thể loại </button>

                <div id="categoriesExpandedContainer" style="display: contents;"><?php for ($i = 5; $i < count($all_categories); $i++):
                        $category = $all_categories[$i];
                        ?>
                    <div class="category-item filter-item hidden-item"
                        style="display: none; padding: 5px 15px; background: #e0e0e0; border-radius: 20px; font-size: 14px; cursor: pointer;"><?php echo htmlspecialchars($category['name']);
                                ?></div><?php endfor;
                    ?>
                </div><?php endif;
            ?>
            </div>
        </div>
        <div class="tags-row" style="margin-top: 10px;">
            <div class="filter-container" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;"><?php $tags_query = "SELECT * FROM tags";
            $tags_result = $conn->query($tags_query);
            $all_tags = [];

            while ($tag = $tags_result->fetch_assoc()) {
                $all_tags[] = $tag;
            }

            // Hiển thị 5 tags đầu tiên
            for ($i = 0; $i < min(5, count($all_tags)); $i++):
                $tag = $all_tags[$i];
                ?>
                <div class="tag-item filter-item"
                    style="padding: 5px 15px; background: #e0e0e0; border-radius: 20px; font-size: 14px; cursor: pointer;"><?php echo htmlspecialchars($tag['name']);
                        ?></div><?php endfor;
            ?><?php if (count($all_tags) > 5): ?><button id="showAllTagsBtn" onclick="toggleTags()"
                    class="show-more-btn"
                    style="padding: 5px 15px; background: #4CAF50; color: white; border: none; border-radius: 20px; font-size: 14px; cursor: pointer; white-space: nowrap;">Tất
                    cả các thẻ </button>
                <div id="tagsExpandedContainer" style="display: contents;"><?php for ($i = 5; $i < count($all_tags); $i++):
                        $tag = $all_tags[$i];
                        ?>
                    <div class="tag-item filter-item hidden-item"
                        style="display: none; padding: 5px 15px; background: #e0e0e0; border-radius: 20px; font-size: 14px; cursor: pointer;"><?php echo htmlspecialchars($tag['name']);
                                ?></div><?php endfor;
                    ?>
                </div><?php endif;
            ?>
            </div>
        </div>
    </div>
    <main class="container">
        <section class="featured-games">
            <div class="game-grid"><?php // Xác định trang hiện tại
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $per_page = 2020;
            $offset = ($page - 1) * $per_page;

            // Lấy tổng số game
            $total_query = "SELECT COUNT(*) as total FROM games";
            $total_result = $conn->query($total_query);
            $total_games = $total_result->fetch_assoc()['total'];
            $total_pages = ceil($total_games / $per_page);

            // Lấy danh sách game với phân trang
            $featured_query = "SELECT * FROM games ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($featured_query);
            $stmt->bind_param("ii", $per_page, $offset);
            $stmt->execute();
            $featured_result = $stmt->get_result();

            if ($featured_result->num_rows > 0):
                while ($game = $featured_result->fetch_assoc()):
                    $gameImage = getGameImage($game['title']);
                    ?><a href="game.php?id=<?php echo $game['id']; ?>" class="game-card"><img
                        src="<?php echo $gameImage; ?>" alt="<?php echo htmlspecialchars($game['title']); ?>"
                        class="game-image">
                    <h3 class="game-title"><?php echo htmlspecialchars($game['title']);
                            ?></h3>
                    <div class="game-overlay">
                        <div class="play-text">Chơi Ngay</div>
                    </div>
                </a><?php endwhile;
            else: ?>
                <p>Chưa có game nổi bật.</p><?php endif;
            ?>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="pagination"><?php if ($page > 1): ?><a href="?page=<?php echo ($page - 1); ?>"
                    class="page-link">Trước</a><?php endif;
                ?><?php for ($i = 1; $i <= $total_pages; $i++): ?><a href="?page=<?php echo $i; ?>"
                    class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i;
                                     ?></a><?php endfor;
                ?><?php if ($page < $total_pages): ?><a href="?page=<?php echo ($page + 1); ?>"
                    class="page-link">Sau</a><?php endif;
                ?></div><?php endif;

            ?>
        </section>
    </main>
    <div class="pre-content pre-content--border pre-content--homepage">
        <div class="container">
            <div class="pre-content__wrapper pre-content__wrapper--first">
                <h1 class="pre-content__title pre-content__title--black">Trò chơi Trực tuyến Miễn phí dành cho Mọi lứa
                    tuổi - Bắt đầu Chơi ngay hôm nay ! </h1>
                <h2 class="pre-content__description">Khám phá các Trò chơi Trực tuyến Miễn phí Hay nhất - Đắm chìm trong
                    thế giới vui nhộn và phiêu lưu. Khám phá hàng nghìn trò chơi thú vị và bắt đầu chơi ngay ! </h2>
            </div>
            <div class="pre-content__wrapper">
                <div class="pre-content__column">
                    <h2 class="pre-content__title pre-content__title--red">Y8 là gì?</h2>
                    <h3>Hơn 15 năm chơi game trên Internet tại Y8.com</h3>
                    <p>Bạn có biết rằng Y8 đã cung cấp <strong>game trực tuyến miễn phí</strong>và <strong>câu
                            đố</strong>từ năm 2006 không? Vậy là hơn 15 năm Y8.com <strong>vui vẻ</strong> ! Cảm ơn bạn
                        đã là một phần của cộng đồng Y8 !</p>
                    <h3>Y8: Trang web trò chơi cuối của bạn</h3>
                    <p>Games Y8 là <strong>nhà phát hành game</strong>và <strong>nhà triển khai game</strong>. Nền tảng
                        Y8 có <strong>mạng xã hội</strong>với 30 triệu người chơi và đang tiếp tục phát triển. Trang web
                        này cũng có <a href="https://vi.y8.com/anim">video để xem</a>như phim hoạt hình,
                        video <strong>cách chơi game</strong>và hướng dẫn <strong>game</strong>. danh mục truyền thông
                        đang phát triển hàng ngày khi <a href="https://vi.y8.com/new/games">trò chơi mới</a>được phát
                        hành hàng giờ. </p>
                    <h3>Sự Phát triển của Game Trình duyệt</h3>
                    <p>Vì Y8.com có lịch sử lâu đời nên chúng tôi đã ghi lại hiện tượng xã hội về <strong>game trình
                            duyệt miễn phí</strong>vì <strong>game</strong>là một phương tiện nghệ thuật quan trọng và
                        có thể giải thích xem mọi người như thế nào trong một khoảng thời gian khác nhau.</p>
                </div>
                <div class="pre-content__column">
                    <h2 class="pre-content__title pre-content__title--blue">Thể loại game</h2>
                    <h3>Danh mục Game Trực tuyến Mới Phát sinh</h3>
                    <p>Trước đây,
                        Y8 nổi tiếng với các thể loại như <strong>game arcade và cổ điển</strong>khi <a
                            href="https://vi.y8.com/tags/bubble_shooter">Bubble Bắn súng</a>là *<em>game trên trình
                            duyệt được chơi nhiều nhất *</em>. Bây giờ,
                        các thể loại khác đã trở nên phổ biến.</p>
                    <h3>Khám phá những cái Hay nhất trong Game nhiều Người chơi</h3>
                    <p>Đáng chú ý,
                        <a href="https://vi.y8.com/tags/2_players">games 2 người</a>đã trở thành <strong>games trên
                            trình duyệt phổ biến</strong>với <a href="https://vi.y8.com/tags/dress_up">games phối
                            đồ</a>. phần <strong>game</strong>quan trọng cuối cùng là <a
                            href="https://vi.y8.com/tags/multiplayer">game nhiều người chơi</a>. Chơi danh mục mở rộng
                        của <strong>game xã hội</strong>được kích hoạt trên <strong>Internet</strong>.
                    </p>
                </div>
                <div class="pre-content__column">
                    <h2 class="pre-content__title pre-content__title--green">Công nghệ</h2>
                    <h3>Điểm đến Yêu thích để chơi Game trên nhiều Thiết bị</h3>
                    <p>Y8.com là ngôi nhà dành cho <strong>game thủ</strong>trên mọi thiết bị. Chơi <a
                            href="https://vi.y8.com/tags/touchscreen">game trên điện thoại</a>hoặc nhận đồ họa 3D phong
                        phú trên máy tính để bàn bằng cách chơi <a href="https://vi.y8.com/tags/webgl">trò chơi
                            webgl</a>.</p>
                    <h3>Mạng trò chơi mở rộng</h3>
                    <p>Mặt khác,
                        nếu sở thích của bạn là thế giới 2D thông thường thì <a href="https://vi.y8.com/tags/html5">game
                            HTML5</a>sẽ phù hợp với bạn. Nếu bạn muốn khơi dậy hoài niệm,
                        hãy truy cập kho lưu trữ <a href="https://vi.y8.com/tags/flash">trò chơi flash</a>kế thừa để
                        biết tất cả <strong>game</strong>mà hiện chưa có ở nơi nào khác.</p>
                    <h3>Kết nối với Cộng đồng Người chơi</h3>
                    <p>Cuối cùng,
                        đừng quên đăng ký <a href="https://account.y8.com/">Tài khoản Y8</a>. Đó là <strong>mạng xã
                            hội</strong>hỗ trợ <strong>cộng đồng người chơi</strong>. </p>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-content">

            <div class="footer-left">
                <div class="footer-logo"><img src="./assets/image_web/logo.png" alt="Logo" class="footer-logo-img">
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
                <div class="social-icons"><a href="#" class="social-icon"><i class="fab fa-twitter"></i></a><a href="#"
                        class="social-icon"><i class="fab fa-facebook"></i></a><a href="#" class="social-icon"><i
                            class="fab fa-instagram"></i></a><a href="#" class="social-icon"><i
                            class="fab fa-youtube"></i></a></div>
            </div>
        </div>
    </footer>
    <script>
    function toggleCategories() {
        const button = document.getElementById('showAllCategoriesBtn');
        const hiddenItems = document.querySelectorAll('.categories-row .hidden-item');
        const isExpanded = button.getAttribute('data-expanded') === 'true';
        const container = document.getElementById('categoriesExpandedContainer');

        hiddenItems.forEach(item => {
                item.style.display = isExpanded ? 'none' : 'block';
            }

        );

        if (isExpanded) {
            button.textContent = 'Xem thêm';
            // Di chuyển nút về vị trí sau item thứ 5
            const fifthItem = document.querySelector('.categories-row .filter-item:nth-child(5)');

            if (fifthItem) {
                fifthItem.after(button);
            }
        } else {
            button.textContent = 'Ẩn bớt';
            // Di chuyển nút về cuối container
            container.after(button);
        }

        button.setAttribute('data-expanded', !isExpanded);
    }

    function toggleTags() {
        const button = document.getElementById('showAllTagsBtn');
        const hiddenItems = document.querySelectorAll('.tags-row .hidden-item');
        const isExpanded = button.getAttribute('data-expanded') === 'true';
        const container = document.getElementById('tagsExpandedContainer');

        hiddenItems.forEach(item => {
                item.style.display = isExpanded ? 'none' : 'block';
            }

        );

        if (isExpanded) {
            button.textContent = 'Xem thêm';
            // Di chuyển nút về vị trí sau item thứ 5
            const fifthItem = document.querySelector('.tags-row .filter-item:nth-child(5)');

            if (fifthItem) {
                fifthItem.after(button);
            }
        } else {
            button.textContent = 'Ẩn bớt';
            // Di chuyển nút về cuối container
            container.after(button);
        }

        button.setAttribute('data-expanded', !isExpanded);
    }
    </script>
</body>

</html>