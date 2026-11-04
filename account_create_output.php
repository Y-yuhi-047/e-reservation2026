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
$picture = $_FILES['icon']['name'] ?? null; // アイコン画像のファイル名を取得
// 最初は「失敗」にしておく
$success = false;

//iconというファイルが送信されたか確認 アップロード時にエラーがなかったか確認
if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    //ファイル名からフォルダ部分を取り除く
    $filename = basename($_FILES['icon']['name']);
    // ファイル名から拡張子を取り出す
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        // 保存するファイル名: user_ + ランダムな英数字 + 拡張子
        // 日本語のファイル名や、同じ名前の上書きを防ぐ
        $newname = 'user_' . md5(time() . $filename) . '.' . $ext;
        $dest = 'uploads/' . $newname;   // 保存先 = DBに入れる文字(uploads/user_xxxx.png)

        try {
            $sql = $pdo->prepare('INSERT INTO user (profile_picture, user_name, student_number, password) VALUES (?, ?, ?, ?)');
            $sql->execute([$picture, $name, $student_number, $password]);

            $success = true;  // ← エラーなく、ここまで来たら成功
        } catch (PDOException $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        if (move_uploaded_file($_FILES['icon']['tmp_name'], $dest) && $success) {
            // アップロード成功
            $picture = $dest;
        } else {
            // アップロード失敗
            $_SESSION['error'] = "<a>アイコン画像のアップロードに失敗しました。</a>";
            if (file_exists($picture)) {
                unlink($picture);
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>アカウント作成完了</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="css/hiroki.css">
</head>

<body>
    <div class="container">
        <main>
            <?php if ($success) : ?>
                <h1>アカウントの作成が<br>完了しました。</h1>
                <img class="result-image" src="images/Gemini_success.jpg" alt="成功キャラクター">
                <form action="login-input.php">
                    <button type="submit" class="btn">ログインページへ</button>
                </form>
            <?php else : ?>
                <h1>アカウントの作成に<br>失敗しました。</h1>
                <a><?php echo $_SESSION['error']; ?></a>
                <img class="result-image" src="images/Gemini_error_character.jpg" alt="エラーキャラクター">
                <form action="account_create_input.php">
                    <button type="submit" class="btn">アカウント作成に戻る</button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>