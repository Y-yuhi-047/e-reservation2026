<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ユーザー名の更新処理
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
        if ($username !== '') {
            $_SESSION['user']['name'] = $username;
        }
    }

    // 2. アイコン画像の保存処理
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        
        // 保存先のディレクトリ（※プロジェクト内に "uploads" フォルダを作成しておいてください）
        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        // ファイル名が被らないようにユニークな名前に変更
        $newFileName = md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // セッションに画像の保存先パスを記録する
            $_SESSION['user']['avatar'] = $dest_path;
        }
    }
}

// プロフィール画面へ戻る
header('Location: profile.php');
exit();