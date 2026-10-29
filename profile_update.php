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

// ログイン中のユーザー識別子（学籍番号）を取得
$loginStudentNumber = $_SESSION['user']['student_number'] ?? $_SESSION['student_number'] ?? 'M1234567';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ユーザー名の更新処理
    if (isset($_POST['username'])) {
        $newUserName = trim($_POST['username']);
        if ($newUserName !== '') {
            // user テーブルの user_name を更新
            $stmt = $pdo->prepare("UPDATE user SET user_name = ? WHERE student_number = ?");
            $stmt->execute([$newUserName, $loginStudentNumber]);
            
            // セッション側の名前も同期しておく
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['name'] = $newUserName;
            }
        }
    }

    // 2. アイコン画像の保存処理
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        
        // 保存先のディレクトリ
        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        // ファイル名が被らないようにユニークな名前に変更
        $newFileName = md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // user テーブルの profile_picture（プロフィール画像）を更新
            $stmt = $pdo->prepare("UPDATE user SET profile_picture = ? WHERE student_number = ?");
            $stmt->execute([$dest_path, $loginStudentNumber]);

            // セッション側の画像パスも同期しておく
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['avatar'] = $dest_path;
            }
        }
    }
}

// プロフィール画面へ戻る
header('Location: profile.php');
exit();