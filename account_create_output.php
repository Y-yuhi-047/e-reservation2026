<?php
session_start();

$pdo = new PDO(
    'mysql:host=localhost;dbname=e-reservation;charset=utf8',
    'root',
    'root'
);

$name = $_POST['name'];
$student_number = $_POST['student_id'];
$password = $_POST['password'];

$picture = 'images/初期設定アイコン.png';

if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $filname = basename($_FILES['icon']['name']);
    $ext = strtolower(pathinfo($filname, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $picture = 'images/' . $filname;

        if (move_uploaded_file($_FILES['icon']['tmp_name'], $picture)) {
            // アップロード成功
            $picture = $dest;
        } else {
            echo "画像のアップロードに失敗しました。";
        }
    }
}

$sql = $pdo->prepare('INSERT INTO user (profile_picture, user_name, student_number, password) VALUES (?, ?, ?, ?)');
$sql->execute([$picture, $name, $student_number, $password]);

echo "<h1>アカウント作成が完了しました。</h1>";
echo "<a href='login-input.php'>ログイン画面に戻る</a>";
