<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- データベース接続設定 ---
$host = 'localhost';
$dbname = 'e-reservation';
$username = 'root';
$password = 'root'; // ※パスワードが設定されていない場合は '' に変更してください

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteId = intval($_POST['game_id'] ?? 0);
    
    if ($deleteId > 0) {
        try {
            // ゲーム本体を削除する
            // ※データベース側で ON DELETE SET NULL が設定されているため、
            //   紐づく予約データの game_id は自動的に NULL になり、履歴は残ります。
            $stmt = $pdo->prepare("DELETE FROM game WHERE game_id = ?");
            $stmt->execute([$deleteId]);

            $_SESSION['flash_message'] = 'ゲームを削除しました（予約履歴は保持されます）。';

        } catch (PDOException $e) {
            $_SESSION['error_message'] = '削除に失敗しました: ' . $e->getMessage();
        }
    }
}

// ゲーム管理画面へリダイレクト
header('Location: game_list.php');
exit();