<?php
// game_organizer.php

class GameOrganizer
{
    private $gamesDir;
    private $gamesList = [];
    private $standardizedGames = [];
    private $dbConnection;

    public function __construct($gamesDir)
    {
        $this->gamesDir = $gamesDir;
        $this->connectDatabase();
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

    public function scanGames()
    {
        $dirs = scandir($this->gamesDir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..')
                continue;

            // Kiểm tra xem có phải là thư mục không
            if (is_dir($this->gamesDir . '/' . $dir)) {
                // Kiểm tra có phải là thư mục số không
                if (is_numeric($dir)) {
                    $this->analyzeNumericGame($dir);
                } else {
                    // Các game đã được chuẩn hóa (breakout, snake, etc.)
                    $this->standardizedGames[] = [
                        'path' => $dir,
                        'title' => ucfirst($dir),
                        'description' => $this->getGameDescription($dir)
                    ];
                }
            }
        }
        return [
            'standardized' => $this->standardizedGames,
            'to_rename' => $this->gamesList
        ];
    }

    private function getGameDescription($dir)
    {
        $indexFile = $this->gamesDir . '/' . $dir . '/index.html';
        if (file_exists($indexFile)) {
            $content = file_get_contents($indexFile);
            // Tìm meta description
            preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $content, $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
            // Nếu không có meta description, tìm trong thẻ title
            preg_match('/<title>(.*?)<\/title>/i', $content, $matches);
            if (isset($matches[1])) {
                return "Game " . $matches[1];
            }
        }
        return "Trò chơi " . ucfirst($dir) . " thú vị";
    }

    private function analyzeNumericGame($dir)
    {
        $gamePath = $this->gamesDir . '/' . $dir;
        $indexFile = $gamePath . '/index.html';

        if (file_exists($indexFile)) {
            // Đọc file index.html để lấy tên game
            $content = file_get_contents($indexFile);
            preg_match('/<title>(.*?)<\/title>/i', $content, $matches);

            $gameName = isset($matches[1]) ? $this->sanitizeGameName($matches[1]) : 'game_' . $dir;

            $this->gamesList[] = [
                'old_path' => $dir,
                'new_path' => $gameName,
                'title' => isset($matches[1]) ? $matches[1] : 'Game ' . $dir,
                'description' => $this->getGameDescription($dir)
            ];
        }
    }

    private function sanitizeGameName($name)
    {
        // Chuyển tên game thành định dạng URL-friendly
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9]+/', '_', $name);
        return trim($name, '_');
    }

    private function checkGameExists($url)
    {
        try {
            $stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM games WHERE url = ?");
            $stmt->execute([$url]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            echo "Lỗi kiểm tra game: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function generateSQLQueries()
    {
        $report = [
            'existing_games' => [],
            'new_games' => [],
            'sql_queries' => []
        ];

        // Kiểm tra các game đã chuẩn hóa
        foreach ($this->standardizedGames as $game) {
            $url = "games/{$game['path']}/index.html";
            if ($this->checkGameExists($url)) {
                $report['existing_games'][] = $game['title'];
            } else {
                $report['new_games'][] = $game['title'];
                $report['sql_queries'][] = sprintf(
                    "INSERT INTO games (title, description, url) VALUES ('%s', '%s', '%s');",
                    addslashes($game['title']),
                    addslashes($game['description']),
                    addslashes($url)
                );
            }
        }

        // Kiểm tra các game cần chuẩn hóa
        foreach ($this->gamesList as $game) {
            $url = "games/{$game['new_path']}/index.html";
            if ($this->checkGameExists($url)) {
                $report['existing_games'][] = $game['title'];
            } else {
                $report['new_games'][] = $game['title'];
                $report['sql_queries'][] = sprintf(
                    "INSERT INTO games (title, description, url) VALUES ('%s', '%s', '%s');",
                    addslashes($game['title']),
                    addslashes($game['description']),
                    addslashes($url)
                );
            }
        }

        return $report;
    }

    public function renameDirectories()
    {
        foreach ($this->gamesList as $game) {
            $oldPath = $this->gamesDir . '/' . $game['old_path'];
            $newPath = $this->gamesDir . '/' . $game['new_path'];

            if (is_dir($oldPath) && !is_dir($newPath)) {
                if (rename($oldPath, $newPath)) {
                    echo "Đã đổi tên thư mục: {$game['old_path']} -> {$game['new_path']}\n";
                } else {
                    echo "Lỗi đổi tên thư mục: {$game['old_path']}\n";
                }
            }
        }
    }
}

// Sử dụng class
try {
    $organizer = new GameOrganizer(__DIR__ . '/games');
    $organizer->scanGames();

    echo "=== Đang kiểm tra games ===\n";
    $result = $organizer->generateSQLQueries();

    echo "\n=== Báo cáo kết quả ===\n";
    echo "Games đã tồn tại trong database:\n";
    foreach ($result['existing_games'] as $game) {
        echo "- $game\n";
    }

    echo "\nGames chưa có trong database:\n";
    foreach ($result['new_games'] as $game) {
        echo "- $game\n";
    }

    if (!empty($result['sql_queries'])) {
        echo "\n=== Câu lệnh SQL để thêm game mới ===\n\n";
        echo "BEGIN;\n\n";
        $allQueries = "";
        foreach ($result['sql_queries'] as $index => $query) {
            $allQueries .= $query . "\n";
        }
        echo $allQueries;
        echo "\nCOMMIT;";
    }

    echo "\n\n=== Đang chuẩn hóa tên thư mục ===\n";
    $organizer->renameDirectories();

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
?>