<?php
// Chỉ khởi tạo session nếu chưa có session nào
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'config.php';
include_once 'refresh_token.php';
include_once 'auth_check.php';
include_once 'header.php';

// Lấy tag_id từ URL
$tag_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Tính toán phân trang
$games_per_page = 20;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $games_per_page;

// Lấy thông tin tag
$tag_query = "SELECT * FROM tags WHERE id = ?";
$stmt = $conn->prepare($tag_query);
$stmt->bind_param("i", $tag_id);
$stmt->execute();
$tag = $stmt->get_result()->fetch_assoc();

if (!$tag) {
    header("Location: index.php");
    exit();
}

// Lấy tổng số game có tag này
$total_query = "SELECT COUNT(DISTINCT g.id) as total 
                FROM games g 
                JOIN game_tag gt ON g.id = gt.game_id 
                WHERE gt.tag_id = ?";
$stmt = $conn->prepare($total_query);
$stmt->bind_param("i", $tag_id);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_games = $total_result['total'];
$total_pages = ceil($total_games / $games_per_page);

// Lấy danh sách games có tag này
$games_query = "SELECT g.*, 
                COUNT(DISTINCT s.id) as play_count
                FROM games g 
                JOIN game_tag gt ON g.id = gt.game_id
                LEFT JOIN scores s ON g.id = s.game_id
                WHERE gt.tag_id = ?
                GROUP BY g.id
                ORDER BY play_count DESC
                LIMIT ? OFFSET ?";

$stmt = $conn->prepare($games_query);
$stmt->bind_param("iii", $tag_id, $games_per_page, $offset);
$stmt->execute();
$games = $stmt->get_result();

// Hàm lấy đường dẫn hình ảnh game
function getGameImage($title)
{
    // Chuẩn hóa tên file từ title (loại bỏ ký tự đặc biệt, khoảng trắng)
    $filename = strtolower(str_replace(' ', ' ', $title)) . '.jpg';

    // Đường dẫn đến thư mục chứa ảnh game
    $image_path = './assets/image_games/' . $filename;

    // Đường dẫn đến ảnh mặc định
    $default_image = './assets/image_games/default-game.png';

    // Kiểm tra xem file ảnh có tồn tại không
    if (file_exists($image_path)) {
        return $image_path;
    }

    // Trả về ảnh mặc định nếu không tìm thấy ảnh game
    return $default_image;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($tag['name']); ?> - Game Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .main-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .view-all-tags {
        margin-bottom: 20px;
        text-align: left;
    }

    .view-all-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 20px;
        background: #c5c7c8;
        color: white;
        border-radius: 50px;
        text-decoration: none;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
        flex-direction: row-reverse;
    }

    .view-all-btn i {
        font-size: 16px;
    }

    .view-all-btn:hover {
        background: #0881a2;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(8, 145, 178, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .view-all-btn {
            padding: 8px 16px;
            font-size: 13px;
        }
    }

    .tag-header {
        display: flex;
        align-items: flex-start;
        gap: 25px;
        margin-bottom: 30px;
    }

    .tag-icon {
        width: 90px;
        height: 90px;
        min-width: 90px;
        background: #e3f2fd;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tag-icon i {
        font-size: 45px;
        color: #0891b2;
    }

    .tag-info {
        flex: 1;
    }

    .tag-name {
        font-size: 32px;
        font-weight: bold;
        color: #333;
        margin: 0 0 10px 0;
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
        <div class="view-all-tags">
            <a href="tags.php" class="view-all-btn">
                Xem tất cả thẻ
            </a>
        </div>
        <div class="tag-header">
            <div class="tag-icon">
                <i class="fas fa-tag"></i>
            </div>
            <div class="tag-info">
                <h1 class="tag-name"><?php echo htmlspecialchars($tag['name']); ?></h1>
                <p class="tag-title"><?php echo htmlspecialchars($tag['title']); ?></p>
                <p class="tag-content"><?php echo htmlspecialchars($tag['content']); ?></p>
            </div>
        </div>

        <div class="games-grid">
            <?php while ($game = $games->fetch_assoc()):
                // Lấy đường dẫn hình ảnh
                $thumbnail = getGameImage($game['title']);
                ?>

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
            <a href="?id=<?php echo $tag_id; ?>&page=<?php echo $current_page - 1; ?>">&laquo; Trước</a>
            <?php endif; ?>

            <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);

                if ($start_page > 1) {
                    echo '<a href="?id=' . $tag_id . '&page=1">1</a>';
                    if ($start_page > 2)
                        echo '<span>...</span>';
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    echo '<a href="?id=' . $tag_id . '&page=' . $i . '"' .
                        ($current_page == $i ? ' class="active"' : '') . '>' . $i . '</a>';
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1)
                        echo '<span>...</span>';
                    echo '<a href="?id=' . $tag_id . '&page=' . $total_pages . '">' . $total_pages . '</a>';
                }

                if ($current_page < $total_pages): ?>
            <a href="?id=<?php echo $tag_id; ?>&page=<?php echo $current_page + 1; ?>">Sau &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>