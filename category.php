<?php
// Chỉ khởi tạo session nếu chưa có session nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';
include_once 'refresh_token.php';
include_once 'auth_check.php';
include_once 'header.php';

// Lấy category_id từ URL
$category_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Tính toán phân trang
$games_per_page = 20; // Số game trên mỗi trang
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $games_per_page;

// Lấy thông tin category
$category_query = "SELECT id, name, title, content FROM categories WHERE id = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    header("Location: index.php");
    exit();
}

// Lấy tổng số game trong category
$total_query = "SELECT COUNT(*) as total FROM games WHERE category_id = ?";
$stmt = $conn->prepare($total_query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_games = $total_result['total'];
$total_pages = ceil($total_games / $games_per_page);

// Lấy danh sách games có phân trang
$games_query = "SELECT g.*, 
                COUNT(DISTINCT s.id) as play_count,
                GROUP_CONCAT(DISTINCT t.name) as tags
                FROM games g 
                LEFT JOIN scores s ON g.id = s.game_id
                LEFT JOIN game_tag gt ON g.id = gt.game_id
                LEFT JOIN tags t ON gt.tag_id = t.id
                WHERE g.category_id = ?
                GROUP BY g.id
                ORDER BY play_count DESC
                LIMIT ? OFFSET ?";

$stmt = $conn->prepare($games_query);
$stmt->bind_param("iii", $category_id, $games_per_page, $offset);
$stmt->execute();
$games = $stmt->get_result();

// Lấy tags phổ biến cho category này
$popular_tags_query = "SELECT t.id,t.name, COUNT(*) as tag_count
                      FROM tags t
                      JOIN game_tag gt ON t.id = gt.tag_id
                      JOIN games g ON gt.game_id = g.id
                      WHERE g.category_id = ?
                      GROUP BY t.id, t.name
                      ORDER BY tag_count DESC
                      LIMIT 5";
$stmt = $conn->prepare($popular_tags_query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$popular_tags = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> - Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .main-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .category-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .category-header {
        display: flex;
        align-items: flex-start;
        gap: 25px;
        margin-bottom: 30px;
    }

    .category-icon {
        width: 90px;
        height: 90px;
        min-width: 90px;
        background: #e3f2fd;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(5, 5, 5, 0.1);
    }

    .category-icon i {
        font-size: 45px;
        color: #0891b2;
    }

    .category-info {
        flex: 1;
    }

    .category-name {

        font-size: 32px;
        font-weight: bold;
        color: #333;
        margin: 0 0 10px 0;
    }

    .category-title {
        font-size: 16px;
        color: #333;
        margin: 0 0 8px 0;
        line-height: 1.4;
    }

    .category-content {
        font-size: 14px;
        color: #666;
        margin: 0;
        line-height: 1.5;
    }

    .popular-tags {

        background: #fff;
        border-radius: 10px;
        flex-direction: column;
        flex-wrap: wrap;
    }

    .popular-tags h1 {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        padding-bottom: 10px;
    }

    .popular-tags h1:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: #0891b2;
        border-radius: 2px;
    }

    .tags-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .tags-container {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 16px;
        color: #0891b2;
        background: white;
        border: 1px solid rgb(207, 206, 206);
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-block;
        text-decoration: none;
    }

    .tags-container:hover {
        background: #f8f9fa;
        border-color: #0891b2;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(8, 145, 178, 0.1);
    }

    @media (max-width: 768px) {
        .popular-tags h1 {
            font-size: 20px;
        }

        .tags-container {
            padding: 6px 12px;
            font-size: 13px;
        }
    }

    .games-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
        margin-top: 20px;
    }

    .game-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .game-card:hover {
        transform: translateY(-5px);
    }

    .game-thumb {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .game-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .game-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 15px;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
        color: white;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .game-card:hover .game-overlay {
        opacity: 1;
    }

    .game-title {
        font-size: 14px;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
        padding: 20px 0;
    }

    .pagination a {
        padding: 8px 16px;
        border-radius: 8px;
        background: #f0f9ff;
        color: #0891b2;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .pagination a:hover {
        background: #e0f2fe;
    }

    .pagination .active {
        background: #0891b2;
        color: white;
    }

    @media (max-width: 1200px) {
        .games-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .category-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="category-header">
            <div class="category-icon">
                <i class="fas fa-gamepad"></i>
            </div>
            <div class="category-info">
                <h1 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="category-title"><?php echo htmlspecialchars($category['title']); ?></p>
                <p class="category-content"><?php echo htmlspecialchars($category['content']); ?></p>
            </div>
            <div class="popular-tags">
                <h1>CÁC THẺ PHỔ BIẾN</h1>
                <div class="tags-wrapper">
                    <?php
                    // Sử dụng kết quả từ $popular_tags query đã có
                    while ($tag = $popular_tags->fetch_assoc()): ?>
                    <a href="list_game_tag.php?id=<?php echo $tag['id']; ?>" class="tags-container">
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="games-grid">
            <?php while ($game = $games->fetch_assoc()):
                $thumbnail = getGameImage($game['title']); ?>
            <div class="game-card">
                <a href="game.php?id=<?php echo $game['id']; ?>">
                    <div class="game-thumb">
                    <img src="<?php echo htmlspecialchars($thumbnail); ?>"
                    alt="<?php echo htmlspecialchars($game['title']); ?>" loading="lazy">
                        <div class="game-overlay">
                            <h3 class="game-title"><?php echo htmlspecialchars($game['title']); ?></h3>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
            <a href="?id=<?php echo $category_id; ?>&page=<?php echo $current_page - 1; ?>">&laquo; Trước</a>
            <?php endif; ?>

            <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);

                if ($start_page > 1) {
                    echo '<a href="?id=' . $category_id . '&page=1">1</a>';
                    if ($start_page > 2) {
                        echo '<span>...</span>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    echo '<a href="?id=' . $category_id . '&page=' . $i . '"' .
                        ($current_page == $i ? ' class="active"' : '') . '>' . $i . '</a>';
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span>...</span>';
                    }
                    echo '<a href="?id=' . $category_id . '&page=' . $total_pages . '">' . $total_pages . '</a>';
                }

                if ($current_page < $total_pages): ?>
            <a href="?id=<?php echo $category_id; ?>&page=<?php echo $current_page + 1; ?>">Sau &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>