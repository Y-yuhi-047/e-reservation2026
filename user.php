<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// セッションにユーザーリストがまだない場合、初期の仮データをセット（imageキーを追加）
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
    // 1. ユーザー削除処理
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = intval($_POST['user_id'] ?? 0);
        $_SESSION['users'] = array_values(array_filter($_SESSION['users'], function($u) use ($deleteId) {
            return $u['id'] !== $deleteId;
        }));
        header('Location: user.php');
        exit();
    }
    
    // 2. パスワードリセット処理
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $_SESSION['flash_message'] = 'パスワードをリセットしました。';
        header('Location: user.php');
        exit();
    }
}

$users = $_SESSION['users'];
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
    <link rel="stylesheet" href="css/style2.css?v=34">
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
    <main class="main-container" style="padding-bottom: 90px;">
        
        <?php if (!empty($flash_message)): ?>
            <div style="background-color: #e8f2ee; color: #1b4d3e; padding: 10px 16px; border-radius: 10px; margin-bottom: 12px; font-size: 13px; font-weight: bold; text-align: center;">
                <?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- セクションタイトルと件数 -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0 4px;">
            <span style="font-size: 13px; font-weight: bold; color: #888888;">登録ユーザー</span>
            <span style="font-size: 13px; color: #888888;"><?php echo count($users); ?>人</span>
        </div>

        <!-- ユーザーリストを囲むカード -->
        <div class="card" style="display: flex; flex-direction: column; padding: 0; overflow: hidden; margin-bottom: 16px;">
            <?php if (empty($users)): ?>
                <div style="padding: 24px 16px; text-align: center; color: #888888; font-size: 14px; width: 100%; box-sizing: border-box;">
                    登録されているユーザーはありません。
                </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <div style="display: flex; flex-direction: column; width: 100%; padding: 16px; box-sizing: border-box; border-bottom: 1px solid #f0f2f1;">
                        
                        <!-- 上段：アイコン、名前、権限 -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <!-- アイコン部分（設定画像があれば画像、なければ頭文字を表示） -->
                                <div style="width: 40px; height: 40px; background-color: #e8f2ee; color: #1b4d3e; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 16px; font-weight: bold; flex-shrink: 0; overflow: hidden;">
                                    <?php if (!empty($user['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($user['initial'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 15px; font-weight: bold; color: #1a1a1a;">
                                    <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <span style="font-size: 12px; color: #888888;">
                                <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <!-- 下段：操作ボタン（パスワードリセット ＆ 削除） -->
                        <div style="display: flex; align-items: center; gap: 8px; padding-left: 54px;">
                            <!-- パスワードリセットボタン -->
                            <form action="user.php" method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>さんのパスワードをリセットしますか？');" style="margin: 0;">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" style="background-color: #ffffff; color: #1b4d3e; border: 1px solid #dce3e0; border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-key" style="font-size: 11px;"></i> パスワードリセット
                                </button>
                            </form>

                            <!-- 削除ボタン -->
                            <form action="user.php" method="POST" onsubmit="return confirm('本当に <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?> さんを削除しますか？');" style="margin: 0;">
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
        <div style="display: flex; align-items: center; gap: 6px; padding: 0 4px; color: #888888; font-size: 12px;">
            <i class="fas fa-shield-alt"></i>
            <span>ユーザーのパスワードリセット・削除を行えます。</span>
        </div>

    </main>


</body>
</html>