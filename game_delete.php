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
            // トランザクション開始
            $pdo->beginTransaction();

            // ① 関連する user_reservation（中間テーブル）のデータを削除
            $stmt = $pdo->prepare("DELETE ur FROM user_reservation ur INNER JOIN reservation r ON ur.reservation_id = r.reservation_id WHERE r.game_id = ?");
            $stmt->execute([$deleteId]);

            // ② 関連する reservation（予約）データを削除
            $stmt = $pdo->prepare("DELETE FROM reservation WHERE game_id = ?");
            $stmt->execute([$deleteId]);

            // ③ 最後に game 本体を削除
            $stmt = $pdo->prepare("DELETE FROM game WHERE game_id = ?");
            $stmt->execute([$deleteId]);

            // コミット
            $pdo->commit();
            $_SESSION['flash_message'] = 'ゲームと関連する予約データを削除しました。';

        } catch (PDOException $e) {
            // 失敗時はロールバック
            $pdo->rollBack();
            $_SESSION['error_message'] = '削除に失敗しました: ' . $e->getMessage();
        }
    }
}

// ゲーム管理画面へリダイレクト
header('Location: game_list.php');
exit();