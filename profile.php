<?php
// フッターで「プロフィール」をアクティブにするための設定
$current_page = 'profile';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール - e-reservation</title>

    <!-- アイコン用のFont Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 共通CSS -->
    <link rel="stylesheet" href="css/style2.css">
</head>

<body class="profile-page">

    <!-- 共通ヘッダーの読み込み -->
    <?php require 'header.php'; ?>

    <!-- メインコンテンツ -->
    <main class="main-container">

        <!-- ユーザー情報カード -->
        <div class="card user-card">
            <div class="user-info">
                <div class="avatar-icon">

                    <!-- ▼ アバター画像（ファイル名に合わせて変更してください） -->
                    <img src="images/user-icon.png" alt="ユーザーアイコン">

                </div>
                <div class="user-details">
                    <h2></h2>
                    <span class="edit-link">プロフィール編集</span>
                </div>
            </div>
            <i class="fas fa-chevron-right arrow-icon"></i>
        </div>

        <!-- 自分の予約メニュー -->
        <div class="card menu-card">
            <div class="menu-left">
                <div class="menu-icon green-bg">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="menu-text">自分の予約</span>
            </div>
            <i class="fas fa-chevron-right arrow-icon"></i>
        </div>

        <!-- ログアウトボタン -->
        <button class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>ログアウト</span>
        </button>

        <!-- 管理者用セクション -->
        <div class="section-title">管理者用</div>

        <!-- 管理者メニューグループ -->
        <div class="card-group">
            <div class="card menu-card inner-card">
                <div class="menu-left">
                    <div class="menu-icon light-green-bg">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="menu-text-group">
                        <span class="menu-title">ユーザー管理</span>
                        <span class="menu-desc">アカウント・パスワード管理</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right arrow-icon"></i>
            </div>

            <div class="card menu-card inner-card">
                <div class="menu-left">
                    <div class="menu-icon light-green-bg">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="menu-text-group">
                        <span class="menu-title">ゲーム管理</span>
                        <span class="menu-desc">登録ゲームの追加・削除</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right arrow-icon"></i>
            </div>
        </div>

    </main>

    <!-- 共通フッターの読み込み -->
    <?php require 'footer.php'; ?>

</body>

</html>