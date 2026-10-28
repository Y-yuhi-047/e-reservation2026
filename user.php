<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// セッションにユーザーリストがまだない場合（仮データを無くす場合は [] にしてください）
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        ['id' => 1, 'name' => '山田 太郎', 'initial' => '山', 'role' => '管理者', 'image' => ''],
        ['id' => 2, 'name' => '田中 一郎', 'initial' => '田', 'role' => '一般', 'image' => ''],
        ['id' => 3, 'name' => '山本 花子', 'initial' => '山', 'role' => '一般', 'image' => ''],
        ['id' => 4, 'name' => '鈴木 しゅん', 'initial' => '鈴', 'role' => '一般', 'image' => ''],
        ['id' => 5, 'name' => '松本 あいこ', 'initial' => '松', 'role' => '一般', 'image' => ''],
    ];
}

// POST送信された場合の処理（削除・パスワードリセット）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = intval($_POST['user_id'] ?? 0);
        $_SESSION['users'] = array_values(array_filter($_SESSION['users'], function($u) use ($deleteId) {
            return $u['id'] !== $deleteId;
        }));
        header('Location: user.php');
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $_SESSION['flash_message'] = 'パスワードをリセットしました。';
        header('Location: user.php');
        exit();
    }
}

$users = $_SESSION['users'];

// 検索キーワードの取得と絞り込み処理
$keyword = trim($_GET['keyword'] ?? '');
if ($keyword !== '') {
    $users = array_values(array_filter($users, function($u) use ($keyword) {
        return mb_stripos($u['name'], $keyword) !== false;
    }));
}

// 並び替え（ソート）処理
$sort = $_GET['sort'] ?? 'new';
if ($sort === 'old') {
    usort($users, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });
} elseif ($sort === 'name') {
    usort($users, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} else {
    usort($users, function($a, $b) {
        return $b['id'] <=> $a['id'];
    });
}

$current_page = 'profile';
$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー管理 - e-reservation</title>
    
    <!-- アイコン用 Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="css/style2.css?v=10">
</head>
<body class="profile-edit-page">

    <!-- ヘッダー -->
    <header class="sub-header">
        <div class="sub-header-left">
            <a href="profile.php" class="back-btn-custom">‹</a>
            <h1 class="sub-header-title">ユーザー管理</h1>
        </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="main-container user-main-container">
        
        <?php if (!empty($flash_message)): ?>
            <div class="flash-message-box">
                <?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- 検索窓 -->
        <form action="user.php" method="GET" class="user-search-form">
            <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="ユーザー名で検索..." class="user-search-input">
            <?php if ($sort !== 'new'): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <button type="submit" class="user-search-btn">検索</button>
        </form>

        <!-- セクションタイトル（左） ＆ 並び替えプルダウン（右） -->
        <div class="user-section-header">
            <div class="user-section-title-group">
                <span class="user-section-label">登録ユーザー</span>
                <span class="user-section-count"><?php echo count($users); ?>人</span>
            </div>

            <!-- 並び替えフォーム -->
            <form action="user.php" method="GET" id="sortForm" class="user-sort-form">
                <?php if ($keyword !== ''): ?>
                    <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <label for="sort" class="user-sort-label">並び順:</label>
                <select name="sort" id="sort" onchange="this.form.submit()" class="user-sort-select">
                    <option value="new" <?php echo ($sort === 'new') ? 'selected' : ''; ?>>新しい順</option>
                    <option value="old" <?php echo ($sort === 'old') ? 'selected' : ''; ?>>古い順</option>
                    <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>名前順</option>
                </select>
            </form>
        </div>

        <!-- ユーザーリストを囲むカード -->
        <div class="card user-card-list">
            <?php if (empty($users)): ?>
                <div class="user-empty-text">
                    該当するユーザーはありません。
                </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-item">
                        
                        <!-- 上段：アイコン、名前、権限 -->
                        <div class="user-item-top">
                            <div class="user-info-group">
                                <div class="user-avatar">
                                    <?php if (!empty($user['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($user['initial'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </div>
                                <span class="user-name">
                                    <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <span class="user-role">
                                <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <!-- 下段：操作ボタン（パスワードリセット ＆ 削除） -->
                        <div class="user-actions">
                            <form action="user.php" method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>さんのパスワードをリセットしますか？');" class="user-inline-form">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="user-btn-reset">
                                    <i class="fas fa-key"></i> パスワードリセット
                                </button>
                            </form>

                            <form action="user.php" method="POST" onsubmit="return confirm('本当に <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?> さんを削除しますか？');" class="user-inline-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="game-delete-btn">削除</button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 下部の注意書き -->
        <div class="user-footer-notice">
            <i class="fas fa-shield-alt"></i>
            <span>ユーザーのパスワードリセット・削除を行えます。</span>
        </div>

    </main>


</body>
</html>