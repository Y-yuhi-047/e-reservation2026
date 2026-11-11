<?php

session_start();

require_once 'password_helper.php';

if (!isset($_SESSION['user'])) {
    // ログインしていない場合はログインページにリダイレクト
    header('Location: login-input.php');
    exit();
}

// データベースに接続する
$pdo = new PDO('mysql:host=localhost;dbname=e-reservation;charset=utf8mb4', 'root', 'root');


// ログイン中のユーザーの学籍番号
$studentNumber = $_SESSION['user']['number'];

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームの入力を受け取る。現在のパスワードは、ログイン時と同じくスペースを除く
    $current = preg_replace('/ [ 　]/u', '', $_POST['current_password'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // 現在のパスワードが正しいか確認
    $stmt = $pdo->prepare('SELECT password FROM user WHERE student_number = ?');
    $stmt->execute([$studentNumber]);
    $dbPassword = $stmt->fetchColumn();

    if ($dbPassword === false || $dbPassword !== $current) {
        // 新しいパスワードと確認用パスワードが一致するか確認
        $error_message = '現在のパスワードが正しくありません。';
    } elseif (mb_strlen($newPassword) < 8 || mb_strlen($newPassword) > 20) {
        $error_message = '新しいパスワードは8文字以上20文字以内で入力してください。';
    } elseif (preg_match('/[ 　]/u', $newPassword)) {
        $error_message = '新しいパスワードにはスペースを含めないでください。';
    } elseif (stripos($newPassword, TEMP_PW_PREFIX) === 0) {
        $error_message = 'このパスワードは使用できません。別のパスワードを入力してください。';
    } elseif ($newPassword !== $confirmPassword) {
        $error_message = '新しいパスワードと確認用パスワードが一致しません。';
    } else {
        // パスワードを更新
        $stmt = $pdo->prepare('UPDATE user SET password = ? WHERE student_number = ?');
        $stmt->execute([$newPassword, $studentNumber]);

        $_SESSION['user']['password'] = $newPassword; // セッションのパスワードも更新
        $_SESSION['if_temp_login'] = false; // 仮パスワードでのログインフラグをリセット
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード変更 - e-reservation</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css">
    <script src="js/hiroki.js" defer></script>
</head>

<body class="password-change-page">


    <header class="sub-header">
        <div class="sub-header-left">
            <a href="profile.php" class="back-btn-custom">‹</a>
            <h1 class="sub-header-title">パスワード変更</h1>
        </div>
    </header>

    <main>
        <?php if ($success) : ?>
            <div class="flash-message-box">パスワードが正常に変更されました。</div>
        <?php else : ?>
            <?php if (!empty($error_message)) : ?>
                <div class="flash-message-box flash-message-box--error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">現在のパスワード</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">新しいパスワード</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">新しいパスワード（確認）</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                </div>

                <div class="password-wrapper">
                    <a type="button" id="togglePassword" class="edit-link">パスワードを表示</a>
                </div>
                <button type="submit">変更する</button>
            </form>
        <?php endif; ?>
    </main>


</body>

</html>