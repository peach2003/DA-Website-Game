<?php
// Chỉ khởi tạo session nếu chưa có session nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';
include_once 'refresh_token.php';
include_once 'auth_check.php';
include_once 'header.php';



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
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Roboto', sans-serif;
    }

    body {
        background: rgb(255, 255, 255);
        color: #1a1a1a;
    }

    .container {
        max-width: 1200px;
        margin: 10px auto;
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
        grid-template-columns: repeat(6, 1fr);
        gap: 1.5rem;
        padding: 1rem;
    }

    .category-item a {
        text-decoration: none;
        color: inherit;
        display: block;
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .category-item:hover a {
        transform: translateY(-2px);
        text-decoration: none;
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

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin: 30px 0;
        background: #ffffff;
        padding: 10px 20px;
        border-radius: 50px;
    }

    .page-link {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        text-decoration: none;
        font-size: 16px;
        font-weight: 500;
        color: #666;
        background: #f0f0f0;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background: #e0e0e0;
        transform: scale(1.05);
    }

    .page-link.active {
        background: #333;
        color: #fff;
    }

    .page-arrow {
        background: #f0f0f0;
        color: #666;
    }

    .page-arrow.disabled {
        background: #f5f5f5;
        color: #ccc;
        cursor: not-allowed;
    }

    .page-ellipsis {
        display: flex;
        align-items: center;
        font-size: 16px;
        color: #666;
        padding: 0 10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pagination {
            gap: 5px;
            padding: 8px 15px;
        }

        .page-link {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
    }

    .pre-content__wrapper {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
    }

    .pre-content {
        background: #ffffff;
        padding: 40px 0;
        margin: 40px 0;
        border: 1px solid #e0e0e0;
    }



    /* Phần đầu tiên (full width) */
    .pre-content__wrapper--first {
        text-align: center;
        padding: 40px 20px;
        margin-bottom: 40px;
        display: flex;
        flex-direction: column;
    }

    .pre-content__title--black {
        color: #333;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .pre-content__description {
        color: #666;
        font-size: 1.1rem;
        font-weight: 400;
        max-width: 900px;
        margin: 0 auto;
        line-height: 1.5;
    }

    /* Phần 3 hàng (mỗi cột là một hàng ngang) */
    .pre-content__columns {
        display: flex;
        flex-direction: row;
        /* Xếp các cột thành hàng dọc */
        gap: 30px;
        padding: 0 20px;
    }

    .pre-content__column {
        padding: 10px;
        width: 100%;
        /* Mỗi cột chiếm toàn bộ chiều rộng */
    }

    .column-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .column-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
    }

    .column-icon--red {
        background: #ff4d4d;
    }

    .column-icon--blue {
        background: #4d94ff;
    }

    .column-icon--green {
        background: #4dd2a5;
    }

    .pre-content__title {
        font-size: 1.3rem;
        font-weight: 700;
    }

    .pre-content__title--red {
        color: #ff4d4d;
    }

    .pre-content__title--blue {
        color: #4d94ff;
    }

    .pre-content__title--green {
        color: #4dd2a5;
    }

    p {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    a {
        color: #4d94ff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    strong {
        color: #333;
    }

    em {
        font-style: italic;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pre-content__wrapper--first {
            padding: 30px 15px;
        }

        .pre-content__title--black {
            font-size: 1.5rem;
        }

        .pre-content__description {
            font-size: 1rem;
        }

        .pre-content__columns {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }
    }

    .filter-section {
        background: #ffffff;
        border-radius: 15px;
        margin: 30px auto;
        padding: 25px;
        max-width: 1300px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    /* Categories and Tags Row */
    .categories-row,
    .tags-row {
        margin-bottom: 20px;
    }

    .filter-container {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Card Item (category-item và tag-item) */
    .filter-item {
        padding: 15px 25px;
        border-radius: 17px;
        font-size: 15px;
        font-weight: 500;
        color: #fff;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .filter-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }

    /* Ẩn các item ban đầu */
    .hidden-item {
        display: none;
    }

    .game-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Nút "Tất cả thể loại" và "Tất cả các thẻ" */
    .show-more-btn {
        padding: 8px 20px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .show-more-btn:hover {
        background: #45a049;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }


    button#showAllCategoriesBtn {
        padding: 15px 20px;
        border-radius: 16px;
        font-size: 15px;
        background: #d9d9d9;
        color: grey;
    }

    button#showAllTagsBtn {
        padding: 15px 20px;
        border-radius: 16px;
        font-size: 15px;
        background: #d9d9d9;
        color: grey;
    }

    button#showAllTagsBtn a {

        color: grey;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-container {
            gap: 8px;
        }

        .filter-item,
        .show-more-btn {
            padding: 6px 15px;
            font-size: 13px;
        }
    }
    </style>
</head>

<body>
    <div class="filter-section">
        <div class="categories-row">
            <div class="filter-container">
                <?php
                $categories_query = "SELECT * FROM categories";
                $categories_result = $conn->query($categories_query);
                $all_categories = [];

                while ($category = $categories_result->fetch_assoc()) {
                    $all_categories[] = $category;
                }

                // Hiển thị tất cả categories (ban đầu ẩn các item từ thứ 6 trở đi)
                for ($i = 0; $i < count($all_categories); $i++):
                    $category = $all_categories[$i];
                    $isHidden = $i >= 6 ? 'hidden-item' : ''; // Ẩn từ item thứ 6 trở đi
                    ?>
                <div class="category-item filter-item <?php echo $isHidden; ?>">
                    <a href="category.php?id=<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                </div>
                <?php endfor; ?>

                <?php if (count($all_categories) > 6): ?>
                <button id="showAllCategoriesBtn" onclick="toggleCategories()" class="show-more-btn">
                    Tất cả thể loại
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="tags-row">
            <div class="filter-container">
                <?php
                $tags_query = "SELECT * FROM tags";
                $tags_result = $conn->query($tags_query);
                $all_tags = [];

                while ($tag = $tags_result->fetch_assoc()) {
                    $all_tags[] = $tag;
                }

                // Hiển thị tất cả tags (ban đầu ẩn các item từ thứ 6 trở đi)
                for ($i = 0; $i < count($all_tags); $i++):
                    $tag = $all_tags[$i];
                    $isHidden = $i >= 7 ? 'hidden-item' : ''; // Ẩn từ item thứ 6 trở đi
                    ?>
                <a href="list_game_tag.php?id=<?php echo $tag['id']; ?>"
                    class="tag-item filter-item <?php echo $isHidden; ?>">
                    <?php echo htmlspecialchars($tag['name']); ?>
                </a>
                <?php endfor; ?>

                <?php if (count($all_tags) > 7): ?>
                <button id="showAllTagsBtn" class="show-more-btn">
                    <a href="tags.php" style="text-decoration: none;">
                        Tất cả các thẻ
                    </a>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <main class="container">
        <section class="featured-games">
            <div class="game-grid">
                <?php
                // Xác định trang hiện tại
                $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                $per_page = 1; // Giảm số lượng game mỗi trang để dễ kiểm tra phân trang
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
                        $thumbnail = getGameImage($game['title']);
                        ?>

                <a href="game.php?id=<?php echo $game['id']; ?>" class="game-card">
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                        alt="<?php echo htmlspecialchars($game['title']); ?>" loading="lazy">
                    <h3 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h3>
                    <div class="game-overlay">
                        <div class="play-text">Chơi Ngay</div>
                    </div>
                </a>
                <?php
                    endwhile;
                else:
                    ?>
                <p>Chưa có game nổi bật.</p>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <!-- Nút "Trước" -->
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>" class="page-link page-arrow">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php else: ?>
                <span class="page-link page-arrow disabled">
                    <i class="fas fa-chevron-left"></i>
                </span>
                <?php endif; ?>

                <!-- Hiển thị số trang -->
                <?php
                    $max_visible_pages = 3; // Số trang tối đa hiển thị trước khi thêm dấu "..."
                    $start_page = max(1, $page - 1);
                    $end_page = min($total_pages, $start_page + $max_visible_pages - 1);

                    // Điều chỉnh start_page nếu gần cuối
                    if ($end_page - $start_page + 1 < $max_visible_pages) {
                        $start_page = max(1, $end_page - $max_visible_pages + 1);
                    }

                    // Hiển thị các số trang
                    for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <!-- Dấu "..." nếu có nhiều trang hơn */
        <?php if ($end_page < $total_pages): ?>
            <span class="page-ellipsis">...</span>
            <a href="?page=<?php echo $total_pages; ?>" class="page-link">
                <?php echo $total_pages; ?>
            </a>
        <?php endif; ?>

        <!-- Nút "Sau" -->
                <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="page-link page-arrow">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php else: ?>
                <span class="page-link page-arrow disabled">
                    <i class="fas fa-chevron-right"></i>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>
    <div class="pre-content pre-content--border pre-content--homepage">
        <div class="container">
            <div class="pre-content__wrapper pre-content__wrapper--first">
                <h1 class="pre-content__title pre-content__title--black">
                    Trò chơi Trực tuyến Miễn phí dành cho Mọi lứa tuổi - Bắt đầu Chơi ngay hôm nay!
                </h1>
                <h2 class="pre-content__description">
                    Khám phá các Trò chơi Trực tuyến Miễn phí Hay nhất - Đắm chìm trong thế giới vui nhộn và phiêu lưu.
                    Khám phá hàng nghìn trò chơi thú vị và bắt đầu chơi ngay!
                </h2>
            </div>
            <div class="pre-content__wrapper pre-content__columns">
                <div class="pre-content__column">
                    <div class="column-header">
                        <span class="column-icon column-icon--red">?</span>
                        <h2 class="pre-content__title pre-content__title--red">Y8 là gì?</h2>
                    </div>
                    <p>
                        Hơn 15 năm chơi game trên Internet tại Y8.com<br>
                        Bạn có biết rằng Y8 đã cung cấp <strong>game trực tuyến miễn phí</strong> và <strong>câu
                            đố</strong> từ năm 2006 không? Vậy là hơn 15 năm Y8.com <strong>vui vẻ</strong>! Cảm ơn bạn
                        đã là một phần của cộng đồng Y8!
                    </p>
                    <p>
                        Y8: Trang web trò chơi cuối của bạn<br>
                        Games Y8 là <strong>nhà phát hành game</strong> và <strong>nhà triển khai game</strong>. Nền
                        tảng Y8 có <strong>mạng xã hội</strong> với 30 triệu người chơi và đang tiếp tục phát triển.
                        Trang web này cũng có <a href="https://vi.y8.com/anim">video để xem</a> như phim hoạt hình,
                        video <strong>cách chơi game</strong> và hướng dẫn <strong>game</strong>. Danh mục truyền thông
                        đang phát triển hàng ngày khi <a href="https://vi.y8.com/new/games">trò chơi mới</a> được phát
                        hành hàng giờ.
                    </p>
                    <p>
                        Sự Phát triển của Game Trình duyệt<br>
                        Vì Y8.com có lịch sử lâu đời nên chúng tôi đã ghi lại hiện tượng xã hội về <strong>game trình
                            duyệt miễn phí</strong> vì <strong>game</strong> là một phương tiện nghệ thuật quan trọng và
                        có thể giải thích xem mọi người như thế nào trong một khoảng thời gian khác nhau.
                    </p>
                </div>
                <div class="pre-content__column">
                    <div class="column-header">
                        <span class="column-icon column-icon--blue">•••</span>
                        <h2 class="pre-content__title pre-content__title--blue">Thể loại game</h2>
                    </div>
                    <p>
                        Danh mục Game Trực tuyến Mới Phát sinh<br>
                        Trước đây, Y8 nổi tiếng với các thể loại như <strong>game arcade và cổ điển</strong> khi <a
                            href="https://vi.y8.com/tags/bubble_shooter">Bubble Bắn súng</a> là <em>game trên trình
                            duyệt được chơi nhiều nhất</em>. Bây giờ, các thể loại khác đã trở nên phổ biến.
                    </p>
                    <p>
                        Khám phá những cái Hay nhất trong Game nhiều Người chơi<br>
                        Đáng chú ý, <a href="https://vi.y8.com/tags/2_players">games 2 người</a> đã trở thành
                        <strong>games trên trình duyệt phổ biến</strong> với <a
                            href="https://vi.y8.com/tags/dress_up">games phối đồ</a>. Phần <strong>game</strong> quan
                        trọng cuối cùng là <a href="https://vi.y8.com/tags/multiplayer">game nhiều người chơi</a>. Chơi
                        danh mục mở rộng của <strong>game xã hội</strong> được kích hoạt trên <strong>Internet</strong>.
                    </p>
                </div>
                <div class="pre-content__column">
                    <div class="column-header">
                        <span class="column-icon column-icon--green">◆</span>
                        <h2 class="pre-content__title pre-content__title--green">Công nghệ</h2>
                    </div>
                    <p>
                        Điểm đến Yêu thích để chơi Game trên nhiều Thiết bị<br>
                        Y8.com là ngôi nhà dành cho <strong>game thủ</strong> trên mọi thiết bị. Chơi <a
                            href="https://vi.y8.com/tags/touchscreen">game trên điện thoại</a> hoặc nhận đồ họa 3D phong
                        phú trên máy tính để bàn bằng cách chơi <a href="https://vi.y8.com/tags/webgl">trò chơi
                            webgl</a>.
                    </p>
                    <p>
                        Mạng trò chơi mở rộng<br>
                        Mặt khác, nếu sở thích của bạn là thế giới 2D thông thường thì <a
                            href="https://vi.y8.com/tags/html5">game HTML5</a> sẽ phù hợp với bạn. Nếu bạn muốn khơi dậy
                        hoài niệm, hãy truy cập kho lưu trữ <a href="https://vi.y8.com/tags/flash">trò chơi flash</a> kế
                        thừa để biết tất cả <strong>game</strong> mà hiện chưa có ở nơi nào khác.
                    </p>
                    <p>
                        Kết nối với Cộng đồng Người chơi<br>
                        Cuối cùng, đừng quên đăng ký <a href="https://account.y8.com/">Tài khoản Y8</a>. Đó là
                        <strong>mạng xã hội</strong> hỗ trợ <strong>cộng đồng người chơi</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php include_once 'footer.php' ?>
    <script>
    const colors = [
        '#ff8c66', // Cam nhạt 
        '#ff66b3', // Hồng 
        '#cc66ff', // Tím 
        '#66ff99', // Xanh lá 
        '#66ccff', // Xanh lam 
        '#ff6666', // Đỏ 
        '#ffcc66', // Vàng 

    ];

    // Lưu trữ màu đã sử dụng
    let usedColors = [];

    // Hàm chọn màu ngẫu nhiên không trùng với màu đã sử dụng
    function getRandomColor(excludeColors) {
        let availableColors = colors.filter(color => !excludeColors.includes(color));
        if (availableColors.length === 0) {
            // Nếu đã dùng hết màu, reset danh sách màu đã sử dụng
            availableColors = [...colors];
            excludeColors.length = 0; // Reset excludeColors
        }
        const randomIndex = Math.floor(Math.random() * availableColors.length);
        const selectedColor = availableColors[randomIndex];
        excludeColors.push(selectedColor); // Thêm màu đã chọn vào danh sách đã sử dụng
        return selectedColor;
    }

    // Áp dụng màu cho các item ban đầu
    function applyInitialColors() {
        const initialItems = document.querySelectorAll('.filter-item:not(.hidden-item)');
        initialItems.forEach(item => {
            const color = getRandomColor(usedColors);
            item.style.backgroundColor = color;
        });
    }

    // Áp dụng màu cho các item ẩn (khi mở rộng)
    function applyHiddenColors(hiddenItems) {
        hiddenItems.forEach(item => {
            const color = getRandomColor(usedColors);
            item.style.backgroundColor = color;
        });
    }

    // Gọi hàm khi trang được tải
    document.addEventListener('DOMContentLoaded', applyInitialColors);

    // Hàm toggleCategories
    function toggleCategories() {
        const button = document.getElementById('showAllCategoriesBtn');
        const hiddenItems = document.querySelectorAll('.categories-row .hidden-item');
        const isExpanded = button.getAttribute('data-expanded') === 'true';

        hiddenItems.forEach(item => {
            item.style.display = isExpanded ? 'none' : 'inline-block';
        });

        if (!isExpanded) {
            // Chỉ áp dụng màu cho các item ẩn khi mở rộng lần đầu
            applyHiddenColors(hiddenItems);
        }

        button.textContent = isExpanded ? 'Tất cả thể loại' : 'Ẩn bớt';
        button.setAttribute('data-expanded', !isExpanded);
    }

    // Hàm toggleTags
    function toggleTags() {
        const button = document.getElementById('showAllTagsBtn');
        const hiddenItems = document.querySelectorAll('.tags-row .hidden-item');
        const isExpanded = button.getAttribute('data-expanded') === 'true';

        hiddenItems.forEach(item => {
            item.style.display = isExpanded ? 'none' : 'inline-block';
        });

        if (!isExpanded) {
            // Chỉ áp dụng màu cho các item ẩn khi mở rộng lần đầu
            applyHiddenColors(hiddenItems);
        }

        button.textContent = isExpanded ? 'Tất cả các thẻ' : 'Ẩn bớt';
        button.setAttribute('data-expanded', !isExpanded);
    }
    </script>
</body>

</html>