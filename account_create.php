<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アカウント作成</title>
    <link rel="stylesheet" href="css/hiroki.css">
    <script src="js/hiroki.js" defer></script>
</head>

<body>

    <div class="container login">

        <div class="header">
            <p class="logo">e-reservation</p>
            <h1>アカウント作成</h1>
        </div>

        <form id="registerForm">

            <!-- アイコン選択エリア -->
            <div class="icon-section">
                <div class="icon-preview" id="iconPreview">
                    <!-- <span id="iconPlaceholder">👤</span> -->
                    <img id="iconImage" src="images/初期設定アイコン.png" alt="アイコン">
                </div>
                <button type="button" id="selectIconBtn" class="btn-secondary">アイコンを追加</button>
                <!-- ファイル選択の標準入力（画面上は隠す） -->
                <input type="file" id="iconInput" accept="image/*" style="display: none;">
            </div>

            <!-- 名前 -->
            <div class="form-group">
                <label for="name">名前</label>
                <input type="text" id="name" placeholder="山田 太郎" required>
            </div>

            <!-- 学生番号 -->
            <div class="form-group">
                <label for="studentId">学生番号</label>
                <input type="text" id="studentId" placeholder="学生番号か教員番号" required>
            </div>

            <!-- パスワード -->
            <div class="form-group">
                <label for="password">パスワード</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="パスワードを入力" required>
                    <button type="button" id="togglePassword" class="btn-toggle-password">表示</button>
                </div>
            </div>


            <!-- 作成ボタン -->
            <button type="submit" class="btn" href="login-input.php">アカウントを作成</button>
        </form>

        <div class="footer">
            <p>すでにアカウントをお持ちの方</p>
            <a href="login-input.php">ログインに戻る</a>
        </div>

    </div>

    <script src="script.js"></script>
</body>

</html>