<?php
session_start();
?>
<?php
require_once 'password_helper.php';
unset($_SESSION['user']);

$pdo = new PDO(
    'mysql:host=localhost;dbname=e-reservation;charset=utf8',
    'root',
    'root'
);

function remove_space($str)
{
    $str = str_replace(" ", "", $str);
    $str = str_replace("　", "", $str);
    return $str;
}

$name = remove_space($_POST['name']);
$student_number = remove_space($_POST['student_number']);
$password = remove_space($_POST['password']);
// 最初は「失敗」にしておく
$success = false;



$sql = $pdo->prepare('select * from user 
                        WHERE REPLACE(REPLACE(user_name, " ", ""), "　", "") = ? 
                        AND student_number = ? 
                        AND password = ?');
$sql->execute([$name, $student_number, $password]);

foreach ($sql as $row) {
    // 変更点：DBに保存されているパスワードが「期限切れの仮パスワード」かを調べる
    // ※入力値ではなくDBの値を使う
    if (isTempPasswordExpired($row['password'])) {
        // 仮パスワードが期限切れの場合はログイン失敗
        $error_message = '仮パスワードの期限が切れています。管理者に再発行を依頼してください。';
        continue; // このユーザーはスキップして次のループへ
    }

    $_SESSION['user'] = [
        'number' => $row['student_number'],
        'profile_picture' => $row['profile_picture'],
        'name' => $row['user_name'],
        'password' => $row['password']
    ];

    // 仮パスワードでログインしたかどうかをセッションに保存
    $_SESSION['if_temp_login'] = (strpos($row['password'], TEMP_PW_PREFIX) === 0);
}
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit();
} else {
    $success = false;
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン完了</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="css/hiroki.css">
</head>

<body>
    <div class="container">
        <main>
            <?php if ($success == false) : ?>
                <h1>ログインに失敗しました。</h1>
                <a>ログイン名またはパスワードが違います。</a>
                <img class="result-image" src="images/Gemini_error_character.jpg" alt="エラーキャラクター">
                <form action="login-input.php">
                    <button type="submit" class="btn">ログインページへ</button>
                </form>
            <?php else : ?>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>