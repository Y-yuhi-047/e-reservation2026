<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>e-reservation</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="css/hiroki.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="js/hiroki.js" defer></script>
</head>

<body>
    <div class="login">
        <div class="header">
            <p class="logo">e-reservation</p>
            <h1>ログイン画面</h1>
        </div>
        <form action="login-output.php" method="post">
            <div class="form-group">
                <label for="name" class="form-label">名前</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="名前を入力" required>
            </div>
            <div class="form-group">
                <label for="student_id" class="form-label">学生番号</label>
                <input type="text" id="student_id" name="student_id" class="form-control" placeholder="学生番号または教員番号を入力" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">パスワード</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="パスワードを入力" required>
                    <button type="button" id="togglePassword" class="btn-toggle-password">表示</button>
                </div>
            </div>

            <button type="submit" class="btn-login">ログイン</button>
            <div class="new-account">
                <a href="account_create.php">アカウントお持ちでない方</a>
            </div>
        </form>
    </div>
</body>

</html>