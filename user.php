<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- データベース接続設定（MAMP等のローカル環境用） ---
$host = 'localhost';
$dbname = 'e-reservation';
$username = 'root';
$password = 'root'; // ※もしパスワードを設定していない場合は ''（空文字）にしてください

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}

// POST送信された場合の処理（削除・パスワードリセット）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = trim($_POST['user_id'] ?? '');
        
        $stmt = $pdo->prepare("DELETE FROM user WHERE student_number = ?");
        $stmt->execute([$deleteId]);

        $_SESSION['flash_message'] = 'ユーザーを削除しました。';
        header('Location: user.php');
        exit();
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $resetId = trim($_POST['user_id'] ?? '');

        $_SESSION['flash_message'] = 'パスワードをリセットしました。';
        header('Location: user.php');
        exit();
    }
}

// 検索キーワードと並び順の取得
$keyword = trim($_GET['keyword'] ?? '');
$sort = $_GET['sort'] ?? 'new';

// SQLの組み立て
$sql = "SELECT * FROM user WHERE 1=1";
$params = [];

if ($keyword !== '') {
    $sql .= " AND user_name LIKE ?";
    $params[] = '%' . $keyword . '%';
}

if ($sort === 'old') {
    $sql .= " ORDER BY student_number ASC";
} elseif ($sort === 'name') {
    $sql .= " ORDER BY user_name ASC";
} else {
    $sql .= " ORDER BY student_number DESC";
}

// データベースからデータ取得
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$raw_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = [];
foreach ($raw_users as $u) {
    $studentId = $u['student_number'] ?? '';
    $name = $u['user_name'] ?? '';
    
    // 学籍番号が "M" で始まる場合は「一般」、それ以外は「管理者」
    if (str_starts_with($studentId, 'M')) {
        $role = '一般';
    } else {
        $role = '管理者';
    }

    $initial = !empty($name) ? mb_substr($name, 0, 1) : 'U';

    $users[] = [
        'id' => $studentId,
        'name' => $name,
        'role' => $role,
        'image' => $u['profile_picture'] ?? '',
        'initial' => $initial
    ];
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
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css?v=44">
</head>
<body class="profile-edit-page">

    <header class="sub-header">
        <div class="sub-header-left">
            <a href="profile.php" class="back-btn-custom">‹</a>
            <h1 class="sub-header-title">ユーザー管理</h1>
        </div>
    </header>

    <main class="main-container user-main-container">
        
        <?php if (!empty($flash_message)): ?>
            <div class="flash-message-box">
                <?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="user.php" method="GET" class="user-search-form">
            <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="ユーザー名で検索..." class="user-search-input">
            <?php if ($sort !== 'new'): ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <button type="submit" class="user-search-btn">検索</button>
        </form>

        <div class="user-section-header">
            <div class="user-section-title-group">
                <span class="user-section-label">登録ユーザー</span>
                <span class="user-section-count"><?php echo count($users); ?>人</span>
            </div>

            <div class="user-sort-dropdown">
                <?php $kw_param = ($keyword !== '') ? '&keyword=' . urlencode($keyword) : ''; ?>
                <button type="button" class="user-sort-toggle-btn" onclick="toggleSortMenu()">
                    <i class="fas fa-arrow-up-down"></i> 並び替え
                </button>
                <div id="sortMenu" class="user-sort-menu">
                    <a href="user.php?sort=new<?php echo $kw_param; ?>" class="<?php echo ($sort === 'new') ? 'active' : ''; ?>">新しい順</a>
                    <a href="user.php?sort=old<?php echo $kw_param; ?>" class="<?php echo ($sort === 'old') ? 'active' : ''; ?>">古い順</a>
                    <a href="user.php?sort=name<?php echo $kw_param; ?>" class="<?php echo ($sort === 'name') ? 'active' : ''; ?>">名前順</a>
                </div>
            </div>
        </div>

        <div class="card user-card-list">
            <?php if (empty($users)): ?>
                <div class="user-empty-text">
                    該当するユーザーはありません。
                </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-item">
                        
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
                                    <span style="font-size: 11px; color: #888; margin-left: 6px;">(<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                </span>
                            </div>
                            <span class="user-role" style="<?php echo ($user['role'] === '管理者') ? 'color: #1b4d3e; font-weight: bold;' : ''; ?>">
                                <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <div class="user-actions">
                            <form action="user.php" method="POST" onsubmit="return confirm('<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>さんのパスワードをリセットしますか？');" class="user-inline-form">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="user-btn-reset">
                                    <i class="fas fa-key"></i> パスワードリセット
                                </button>
                            </form>

                            <form action="user.php" method="POST" onsubmit="return confirm('本当に <?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?> さんを削除しますか？');" class="user-inline-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="game-delete-btn">削除</button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="user-footer-notice">
            <i class="fas fa-shield-alt"></i>
            <span>ユーザーのパスワードリセット・削除を行えます。</span>
        </div>

    </main>


    <script>
    function toggleSortMenu() {
        const menu = document.getElementById('sortMenu');
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }

    window.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.user-sort-dropdown');
        const menu = document.getElementById('sortMenu');
        if (dropdown && !dropdown.contains(e.target)) {
            menu.style.display = 'none';
        }
    });
    </script>

</body>
</html>