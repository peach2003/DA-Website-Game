<?php
// get_games.php
header('Content-Type: application/json');

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=game_portal",
        "root",
        "",
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT id, title, url FROM games");
    $games = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        preg_match('/games\/(.*?)\/index\.html/', $row['url'], $matches);
        if (isset($matches[1])) {
            $games[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'dir' => $matches[1]
            ];
        }
    }

    echo json_encode($games);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>