<?php
// もしページ名が指定されていない場合の初期値
$current_page = $current_page ?? '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-reservation</title>
    
    <!-- アイコン用のFont Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style2.css">
</head>
<nav class="mobile-footer">
    <!-- ホーム -->
    <a href="home.php" class="footer-item <?php echo ($current_page === 'home') ? 'active' : ''; ?>">
        <i class="fas fa-home" style="font-size: 20px; margin-bottom: 4px;"></i>
        <span>ホーム</span>
    </a>

    <!-- 新規予約 -->
    <a href="create.php" class="footer-item <?php echo ($current_page === 'create') ? 'active' : ''; ?>">
        <i class="fas fa-plus-circle" style="font-size: 20px; margin-bottom: 4px;"></i>
        <span>新規予約</span>
    </a>

    <!-- 予約一覧 -->
    <a href="list.php" class="footer-item <?php echo ($current_page === 'list') ? 'active' : ''; ?>">
        <i class="fas fa-calendar-alt" style="font-size: 20px; margin-bottom: 4px;"></i>
        <span>予約一覧</span>
    </a>

    <!-- プロフィール -->
    <a href="profile.php" class="footer-item <?php echo ($current_page === 'profile') ? 'active' : ''; ?>">
        <i class="fas fa-user" style="font-size: 20px; margin-bottom: 4px;"></i>
        <span>プロフィール</span>
    </a>

    <!-- iPhoneのホームインジケータ（バー） -->
    <div class="home-indicator"></div>
</nav>