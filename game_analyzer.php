<?php
class GameAnalyzer
{
    private $gameDir;
    private $gamePath;
    private $gameId;
    private $jsFiles = [];
    private $scorePatterns = [
        'score' => ['window.score', 'score', 'points', 'gameScore', 'playerScore'],
        'functions' => ['updateScore', 'setScore', 'addScore', 'scoreUpdate', 'playerScore']
    ];
    private $dbConnection;

    public function __construct($gameDir, $gameId)
    {
        $this->gameDir = $gameDir;
        $this->gameId = $gameId;
        $this->gamePath = __DIR__ . '/games/' . $gameDir;
        $this->findJsFiles($this->gamePath);
        $this->connectDatabase();
    }

    private function findJsFiles($dir)
    {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..')
                continue;

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->findJsFiles($path);
            } else if (pathinfo($file, PATHINFO_EXTENSION) === 'js') {
                $this->jsFiles[] = $path;
            }
        }
    }

    private function connectDatabase()
    {
        try {
            $this->dbConnection = new PDO(
                "mysql:host=localhost;dbname=game_portal",
                "root",
                "",
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Lỗi kết nối database: " . $e->getMessage());
        }
    }

    private function getGamesFromDatabase()
    {
        try {
            $stmt = $this->dbConnection->query("SELECT id, title, url FROM games");
            $games = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Lấy tên thư mục từ URL (games/dirname/index.html)
                preg_match('/games\/(.*?)\/index\.html/', $row['url'], $matches);
                if (isset($matches[1])) {
                    $games[] = [
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'dir' => $matches[1]
                    ];
                }
            }
            return $games;
        } catch (PDOException $e) {
            echo "Lỗi truy vấn database: " . $e->getMessage();
            return [];
        }
    }

    public function analyze()
    {
        $result = [
            'game_id' => $this->gameId,
            'game_dir' => $this->gameDir,
            'js_files' => count($this->jsFiles),
            'score_tracking' => false,
            'score_variables' => [],
            'score_functions' => [],
            'needs_modification' => true,
            'modification_applied' => false
        ];

        foreach ($this->jsFiles as $file) {
            $content = file_get_contents($file);

            // Tìm biến điểm số
            foreach ($this->scorePatterns['score'] as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $result['score_variables'][] = $pattern;
                }
            }

            // Tìm hàm xử lý điểm số
            foreach ($this->scorePatterns['functions'] as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $result['score_functions'][] = $pattern;
                }
            }

            // Kiểm tra xem đã có getScore chưa
            if (strpos($content, 'window.getScore') !== false) {
                $result['score_tracking'] = true;
                $result['needs_modification'] = false;
            }
        }

        // Nếu cần sửa đổi, thêm code theo dõi điểm
        if ($result['needs_modification'] && !empty($result['score_variables'])) {
            $this->modifyGame($result);
            $result['modification_applied'] = true;
        }

        return $result;
    }

    private function modifyGame($analysis)
    {
        foreach ($this->jsFiles as $file) {
            $content = file_get_contents($file);

            // Tìm file JS chính (thường chứa biến score)
            foreach ($analysis['score_variables'] as $scoreVar) {
                if (strpos($content, $scoreVar) !== false) {
                    // Thêm code theo dõi điểm
                    $scoreTracker = "\n\n// Auto-generated score tracker
window.getScore = function() {
    return typeof {$scoreVar} !== 'undefined' ? {$scoreVar} : 0;
};

// Score change observer
setInterval(function() {
    var currentScore = window.getScore();
    if (currentScore > 0) {
        window.parent.postMessage(currentScore, '*');
    }
}, 1000);\n";

                    // Thêm vào cuối file
                    file_put_contents($file, $content . $scoreTracker);
                    break;
                }
            }
        }
    }
}

// API endpoint để phân tích game
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $gameDir = $_POST['game_dir'] ?? '';
    $gameId = $_POST['game_id'] ?? 0;

    if (empty($gameDir) || empty($gameId)) {
        echo json_encode(['error' => 'Thiếu thông tin game']);
        exit;
    }

    $analyzer = new GameAnalyzer($gameDir, $gameId);
    $result = $analyzer->analyze();

    echo json_encode($result);
    exit;
}

// Giao diện web để kiểm tra game
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Game Analyzer</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    .game-item {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ddd;
    }

    .success {
        color: green;
    }

    .warning {
        color: orange;
    }

    .error {
        color: red;
    }

    #loading {
        display: none;
    }

    .progress {
        margin: 10px 0;
    }
    </style>
</head>

<body>
    <h1>Game Analyzer</h1>
    <div id="loading">Đang phân tích games...</div>
    <div class="progress"></div>
    <div id="results"></div>

    <script>
    let totalGames = 0;
    let analyzedGames = 0;

    async function getGamesFromDatabase() {
        try {
            const response = await fetch('get_games.php');
            const games = await response.json();
            return games;
        } catch (error) {
            console.error('Lỗi khi lấy danh sách game:', error);
            return [];
        }
    }

    async function analyzeGame(gameDir, gameId) {
        try {
            const formData = new FormData();
            formData.append('game_dir', gameDir);
            formData.append('game_id', gameId);

            const response = await fetch('game_analyzer.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            displayResult(result);
            updateProgress();
        } catch (error) {
            console.error('Lỗi khi phân tích game:', error);
        }
    }

    function updateProgress() {
        analyzedGames++;
        const progress = document.querySelector('.progress');
        progress.textContent = `Đã phân tích ${analyzedGames}/${totalGames} games`;

        if (analyzedGames === totalGames) {
            document.getElementById('loading').style.display = 'none';
        }
    }

    function displayResult(result) {
        const div = document.createElement('div');
        div.className = 'game-item';

        let status = result.score_tracking ? 'success' : (result.modification_applied ? 'warning' : 'error');

        div.innerHTML = `
            <h3>Game: ${result.game_dir} (ID: ${result.game_id})</h3>
            <p class="${status}">
                ${result.score_tracking ? 'Game đã có sẵn theo dõi điểm' :
                    (result.modification_applied ? 'Đã thêm code theo dõi điểm' :
                        'Không thể xác định hoặc thêm theo dõi điểm')}
            </p>
            <p>Số file JS: ${result.js_files}</p>
            <p>Biến điểm số tìm thấy: ${result.score_variables.join(', ') || 'Không tìm thấy'}</p>
            <p>Hàm xử lý điểm số: ${result.score_functions.join(', ') || 'Không tìm thấy'}</p>
        `;

        document.getElementById('results').appendChild(div);
    }

    // Khởi động phân tích
    async function startAnalysis() {
        document.getElementById('loading').style.display = 'block';
        const games = await getGamesFromDatabase();
        totalGames = games.length;

        for (const game of games) {
            await analyzeGame(game.dir, game.id);
        }
    }

    // Bắt đầu khi trang đã tải xong
    document.addEventListener('DOMContentLoaded', startAnalysis);
    </script>
</body>

</html>