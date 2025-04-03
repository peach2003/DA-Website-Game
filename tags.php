<?php
include_once 'header.php';
?>

<style>
.tags-page {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.95);
}

.tags-page-header {
    text-align: start;
    margin-bottom: 20px;
    padding: 10px;
}

.tags-page-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 15px;
}

.tags-page-description {
    color: #666;
    font-size: 1.1rem;
    max-width: 800px;
}

.tags-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
}

.tag-item {
    padding: 10px 20px;
    border-radius: 15px;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    /* Chuyển màu chữ thành đen */
    background-color: #fff;
    /* Nền trắng */
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    border: 2px solid;
    /* Thêm border */
}

.tag-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background-color: rgba(0, 0, 0, 0.05);
    /* Thêm màu nền nhạt khi hover */
}
</style>

<div class="tags-page">
    <div class="tags-page-header">
        <h1 class="tags-page-title">TẤT CẢ CÁC THỂ LOẠI GAME MIỄN PHÍ.</h1>
        <p class="tags-page-description">
            Bạn đang tìm kiếm một thể loại trò chơi nhất định? Kiểm tra danh sách mở rộng các loại trò chơi tại Y8
            Games. Chúng tôi đã gắn nhãn các trò chơi bằng cách sử dụng các tag và danh mục trong hơn một thập kỷ qua.
            Trang này liệt kê hàng trăm tag khác nhau đại diện cho toàn bộ các bộ sưu tập trò chơi có thể chơi trên
            trình duyệt.
        </p>
    </div>

    <div class="tags-grid">
        <?php
        $tags_query = "SELECT * FROM tags ORDER BY name";
        $tags_result = $conn->query($tags_query);

        while ($tag = $tags_result->fetch_assoc()):
            // Tạo màu ngẫu nhiên cho border của thẻ
            $colors = [
                '#ff8c66', '#ff66b3', '#cc66ff', '#66ff99', 
                '#66ccff', '#ff6666', '#ffcc66'
            ];
            $randomColor = $colors[array_rand($colors)];
        ?>
        <div class="tag-item" style="border-color: <?php echo $randomColor; ?>">
            <?php echo htmlspecialchars($tag['name']); ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include_once 'footer.php'; ?>

<script>
// Có thể thêm các hiệu ứng JavaScript nếu cần
</script>