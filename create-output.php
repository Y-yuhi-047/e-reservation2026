<?php
// POSTで送られてきた全データを一覧表示
print_r($_POST);

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=e-reservation;charset=utf8mb4',
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

} catch (PDOException $e) {
    exit('データベースエラー: ' . $e->getMessage());
}

?>