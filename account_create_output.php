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


if (strlen($student_number) < 8 || strlen($password) < 8) {

    $_SESSION['error'] = "<h1>学生番号とパスワードは8文字以上で入力してください。</h1>";
    header('Location: account_create_input.php');
    exit();
}

if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['icon']['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        // 保存するファイル名: user_ + ランダムな英数字 + 拡張子
        // 日本語のファイル名や、同じ名前の上書きを防ぐ
        $newname = 'user_' . md5(time() . $filename) . '.' . $ext;
        $dest = 'uploads/' . $newname;   // 保存先 = DBに入れる文字(uploads/user_xxxx.png)


        if (move_uploaded_file($_FILES['icon']['tmp_name'], $dest)) {
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
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>アカウント作成完了</title>
    <meta name="description" content="">
</head>

<body>
    <div class="container login">

    </div>
</body>

</html>