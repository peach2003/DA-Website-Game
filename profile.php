<?php
session_start();
require_once 'config.php';

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Nếu là upload avatar
    if (isset($_FILES['avatar'])) {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Vui lòng đăng nhập');
            }

            $file = $_FILES['avatar'];
            $user_id = $_SESSION['user_id'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Lỗi upload file');
            }
            
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('File không được vượt quá 5MB');
            }
            
            if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/jpg'])) {
                throw new Exception('Chỉ chấp nhận file ảnh JPG, JPEG hoặc PNG');
            }
            
            // Lấy thông tin avatar cũ
            $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $old_avatar = $stmt->get_result()->fetch_assoc()['avatar'];

            // Tạo tên file mới
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('avatar_') . '.' . $extension;
            $upload_path = 'assets/image_avatars/' . $new_filename;
            
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Không thể lưu file');
            }
            
            // Cập nhật database
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->bind_param("si", $new_filename, $user_id);
            
            if ($stmt->execute()) {
                // Xóa avatar cũ nếu không phải avatar mặc định
                if ($old_avatar && 
                    $old_avatar !== 'default-avatar.png' && 
                    $old_avatar !== 'male.jpg' && 
                    $old_avatar !== 'female.png') {
                    $old_file_path = 'assets/image_avatars/' . $old_avatar;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                die(json_encode(['success' => true]));
            } else {
                unlink($upload_path);
                throw new Exception('Không thể cập nhật database');
            }
            
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }
    
    // Nếu là cập nhật thông tin cá nhân
    if (isset($_POST['update_profile'])) {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Vui lòng đăng nhập');
            }

            $user_id = $_SESSION['user_id'];
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
            $birthday = filter_input(INPUT_POST, 'birthday', FILTER_SANITIZE_STRING);

            if (!$email) {
                throw new Exception('Email không hợp lệ');
            }

            if (!in_array($gender, ['male', 'female', 'other'])) {
                throw new Exception('Giới tính không hợp lệ');
            }

            // Kiểm tra email đã tồn tại
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Email đã được sử dụng');
            }

            // Cập nhật thông tin
            $stmt = $conn->prepare("UPDATE users SET email = ?, gender = ?, birthday = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $gender, $birthday, $user_id);

            if (!$stmt->execute()) {
                throw new Exception('Không thể cập nhật thông tin');
            }

            die(json_encode(['success' => true]));

        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }
    }
}

include 'auth_check.php';
include_once 'header.php';

// Yêu cầu đăng nhập để truy cập trang này
requireAuth();

// Lấy thông tin người dùng từ database
$user_id = $_SESSION['user_id']; // Lấy user_id từ session


// Lấy thông tin chi tiết từ database
$stmt = $conn->prepare("SELECT id, username, email, avatar, role, gender, birthday, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Lấy số lượng điểm cao của người dùng
$stmt = $conn->prepare("SELECT COUNT(*) as total_scores FROM scores WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$scores_count = $stmt->get_result()->fetch_assoc()['total_scores'];

// Lấy điểm cao nhất của người dùng
$stmt = $conn->prepare("
    SELECT g.title, s.score, s.created_at 
    FROM scores s 
    JOIN games g ON s.game_id = g.id 
    WHERE s.user_id = ? 
    ORDER BY s.score DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$top_scores = $stmt->get_result();


// Hàm chuyển đổi giới tính từ tiếng Anh sang tiếng Việt
function translateGender($gender)
{
    switch ($gender) {
        case 'male':
            return 'Nam';
        case 'female':
            return 'Nữ';
        default:
            return 'Khác';
    }
}

// Hàm định dạng ngày tháng sang định dạng Việt Nam
function formatDate($date)
{
    if (!$date)
        return 'Chưa cập nhật';
    return date('d/m/Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ của <?php echo htmlspecialchars($username); ?> - Game Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        background-color: #edeff2;
        font-family: 'Roboto', sans-serif;
        color: #333;
    }

    .profile-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 20px;
        background: #edeff2;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        background: linear-gradient(135deg, #f8efc7 29%, #ac46e6 100%);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .avatar-container {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        cursor: pointer;
        border: 3px solid #81abe8;
    }

    .avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .avatar-container:hover .avatar-overlay {
        opacity: 1;
    }

    .avatar-container:hover .avatar {
        transform: scale(1.1);
    }

    .change-avatar-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: white;
        cursor: pointer;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }

    .avatar-container:hover .change-avatar-btn {
        transform: translateY(0);
    }

    .change-avatar-btn i {
        font-size: 24px;
        margin-bottom: 5px;
    }

    .change-avatar-btn span {
        font-size: 14px;
        font-weight: 500;
    }

    .user-info-profile {
        display: flex;
        gap: 50px;
    }

    .username {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #2d3436;
    }

    .user-stats {
        display: flex;
        gap: 0rem;
        margin-top: 1rem;
        flex-direction: column;
        align-items: center;
    }

    .user-name-role {
        display: flex;
        gap: 0rem;
        margin-top: 1rem;
        flex-direction: column;
        align-items: center;
    }

    .user-name-role h1 {
        font-size: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #0984e3;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #636e72;
    }

    .profile-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .content-card {
        background: #fff;
        padding: 1.5rem;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .card-title {
        font-size: 1.2rem;
        color: #2d3436;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-direction: row;
    }

    .card-title i {
        color: #0984e3;
    }

    .score-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .score-item:last-child {
        border-bottom: none;
    }

    .score-game {
        font-weight: 500;
    }

    .score-value {
        color: #0984e3;
        font-weight: bold;
    }

    .score-date {
        font-size: 0.8rem;
        color: #636e72;
    }

    .badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .badge-admin {
        background-color: #ff7675;
        color: white;
    }

    .badge-user {
        background-color: #74b9ff;
        color: white;
    }

    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }

        .user-stats {
            justify-content: center;
        }

        .avatar {
            width: 120px;
            height: 120px;
        }
    }

    .info-grid {
        display: flex;
        align-items: flex-start;
        /* gap: 1rem; */
        /* padding: 1.5rem; */
        background: #ffffff;
        border-radius: 15px;
        flex-direction: column;
    }

    .info-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.5rem;
    }

    .info-item:last-child {
        border-right: none;
    }

    .info-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
        white-space: nowrap;
    }

    .info-label {
        font-size: 1rem;
        color: #0984e3;
        font-weight: 500;
        white-space: nowrap;
    }

    .edit-info-btn {
        float: right;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 2px 17px;
        border: none;
        border-radius: 50px;
        background-color: #0b8ef1;
        color: white;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .edit-info-btn:hover {
        background-color: rgb(245, 52, 30);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .edit-info-btn i {
        font-size: 16px;
        color: white;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
        /* Thêm flexbox để căn giữa */
        display: none;
        justify-content: center;
        align-items: center;
    }

    .modal.show {
        opacity: 1;
        display: flex;
        /* Khi hiển thị modal, dùng flex để căn giữa */
    }

    .modal-content {
        position: relative;
        background-color: #fff;
        padding: 30px;
        width: 90%;
        max-width: 500px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        transform: translateY(50px);
        /* Giảm khoảng cách di chuyển */
        opacity: 0;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        /* Xóa margin vì đã dùng flexbox căn giữa */
        margin: 0;
    }

    .modal.show .modal-content {
        transform: translateY(0);
        opacity: 1;
    }

    .modal.hiding .modal-content {
        transform: translateY(50px);
        opacity: 0;
    }

    /* Thêm media query để điều chỉnh cho màn hình nhỏ */
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            padding: 20px;
            margin: 10px;
            /* Thêm margin nhỏ cho mobile để không sát viền */
        }
    }

    /* Tùy chỉnh thêm cho form bên trong */
    .modal h3 {
        margin-bottom: 25px;
        color: #2d3436;
        font-size: 24px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2d3436;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 2px solid #dadbdc;
        border-radius: 17px;
        font-size: 18px;
        transition: all 0.3s ease;
        text-overflow: revert-layer;

    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #0984e3;
        outline: none;
        box-shadow: 0 0 0 3px rgba(9, 132, 227, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-cancel,
    .btn-update {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-cancel {
        background-color: #ff0000;
        color: #ffffff;
    }

    .btn-update {
        background-color: #0b8ef1;
        color: white;
    }

    .btn-cancel:hover {
        background-color: #dee2e6;
        transform: translateY(-2px);
    }

    .btn-update:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(9, 132, 227, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-content {
            margin: 10% auto;
            padding: 20px;
        }

        .btn-cancel,
        .btn-update {
            padding: 10px 20px;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .info-grid {
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
        }

        .info-item {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }

        .info-item:last-child {
            border-bottom: none;
        }
    }
    </style>
</head>

<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="avatar-container">
                <img src="<?php echo htmlspecialchars(getAvatarUrl($user_data)); ?>" alt="Avatar" class="avatar">
                <div class="avatar-overlay">
                    <label for="avatar-upload" class="change-avatar-btn">
                        <i class="fas fa-camera"></i>
                        <span>Thay đổi</span>
                    </label>
                    <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
                </div>
            </div>
            <div class="user-info-profile">
                <div class="user-name-role">
                    <h1 class="username"><?php echo htmlspecialchars($user['username']); ?></h1>
                    <span class="badge <?php echo $user_data['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                        <?php echo ucfirst(htmlspecialchars($user_data['role'])); ?>
                    </span>
                </div>
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo date('d/m/Y', strtotime($user_data['created_at'])); ?></div>
                        <div class="stat-label">Ngày tham gia</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="content-card">
                <h2 class="card-title">
                    <i class="fas fa-user"></i>
                    Thông tin cá nhân
                    <button class="edit-info-btn">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa
                    </button>
                </h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Email:</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($user_data['email'] ?? 'Chưa cập nhật'); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Giới tính:</div>
                        <div class="info-value">
                            <?php echo translateGender($user_data['gender'] ?? 'other'); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ngày sinh:</div>
                        <div class="info-value">
                            <?php echo formatDate($user_data['birthday'] ?? null); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thêm modal chỉnh sửa thông tin -->
            <div id="editProfileModal" class="modal">
                <div class="modal-content">
                    <h3>Chỉnh sửa thông tin cá nhân</h3>
                    <form id="editProfileForm">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Giới tính:</label>
                            <select id="gender" name="gender">
                                <option value="male" <?php echo ($user_data['gender'] == 'male') ? 'selected' : ''; ?>>
                                    Nam</option>
                                <option value="female"
                                    <?php echo ($user_data['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                <option value="other"
                                    <?php echo ($user_data['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="birthday">Ngày sinh:</label>
                            <input type="date" id="birthday" name="birthday"
                                value="<?php echo $user_data['birthday'] ?? ''; ?>">
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel">Hủy</button>
                            <button type="submit" class="btn-update">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="content-card">
                <h2 class="card-title">
                    <i class="fas fa-trophy"></i>
                    Điểm cao nhất
                </h2>
                <?php if ($top_scores->num_rows > 0): ?>
                <?php while ($score = $top_scores->fetch_assoc()): ?>
                <div class="score-item">
                    <div>
                        <div class="score-game"><?php echo htmlspecialchars($score['title']); ?></div>
                        <div class="score-date"><?php echo date('d/m/Y', strtotime($score['created_at'])); ?></div>
                    </div>
                    <div class="score-value"><?php echo number_format($score['score']); ?></div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <p>Chưa có điểm số nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('avatar-upload').addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        try {
            if (file.size > 5 * 1024 * 1024) {
                throw new Error('File không được vượt quá 5MB');
            }

            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                throw new Error('Chỉ chấp nhận file ảnh JPG, JPEG hoặc PNG');
            }

            const formData = new FormData();
            formData.append('avatar', file);

            const response = await fetch('profile.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Lỗi kết nối server');
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message);
            }

            // Reload trang sau khi upload thành công
            window.location.reload();

        } catch (error) {
            alert(error.message || 'Có lỗi xảy ra khi cập nhật avatar');
        } finally {
            // Reset input file
            e.target.value = '';
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('editProfileModal');
        const editBtn = document.querySelector('.edit-info-btn');
        const cancelBtn = document.querySelector('.btn-cancel');
        const form = document.getElementById('editProfileForm');

        // Hàm mở modal
        function openModal() {
            modal.style.display = 'flex'; // Thay đổi thành flex
            // Trigger reflow
            modal.offsetHeight;
            modal.classList.add('show');
        }

        // Hàm đóng modal
        function closeModal() {
            modal.classList.add('hiding');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.classList.remove('hiding');
            }, 500);
        }

        // Các event listeners
        editBtn.addEventListener('click', openModal);
        cancelBtn.addEventListener('click', closeModal);

        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Xử lý submit form
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const formData = new FormData(form);
                const response = await fetch('profile.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message);
                }

                closeModal();
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } catch (error) {
                alert(error.message || 'Có lỗi xảy ra khi cập nhật thông tin');
            }
        });
    });
    </script>

    <?php include_once 'footer.php'; ?>
</body>

</html>