<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>e-reservation</title>
    <meta name="description" content="">
</head>

<body>
    <div class="login">
        <h3>e-reservation</h3>
        <h2>ログイン</h2>
        <form action="login-output.php" method="post">
            <div class="form-group">
                <label for="email" class="form-label">メールアドレス</label>
                <input type="email" id="email" class="form-control" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">パスワード</label>
                <input type="password" id="password" class="form-control" placeholder="パスワードを入力" required>
            </div>

            <button type="submit" class="btn-login">ログイン</button>
            <div class="new-account">
                <a href="#">アカウントお持ちでない方</a>
            </div>
        </form>
    </div>
</body>

</html>