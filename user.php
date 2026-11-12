<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'password_helper.php';

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

        $check = $pdo->prepare("SELECT COUNT(*) FROM user_reservation WHERE student_number = ?");
        $check->execute([$deleteId]);

        if ($check->fetchColumn() > 0) {
            $_SESSION['flash_message'] = 'このユーザーは予約情報が存在するため、削除できません。';
            header('Location: user.php');
            exit();
        } else {
            try {
                $pic = $pdo->prepare("SELECT profile_picture FROM user WHERE student_number = ?");
                $pic->execute([$deleteId]);
                $profilePicture = $pic->fetchColumn();


                $stmt = $pdo->prepare("DELETE FROM user WHERE student_number = ?");
                $stmt->execute([$deleteId]);

                if ($profilePicture && strpos($profilePicture, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $profilePicture)) {
                    unlink(__DIR__ . '/' . $profilePicture);
                }

                $_SESSION['flash_message'] = 'ユーザーを削除しました。';
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = 'ユーザーの削除に失敗しました。' . $e->getMessage();
            }
        }

        header('Location: user.php');
        exit();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $resetId = trim($_POST['user_id'] ?? '');

        // リセット対象のユーザー名をDBから探す（画面に「〇〇さんの仮パスワード」と出すため）
        $nameStmt = $pdo->prepare("SELECT user_name FROM user WHERE student_number = ?");
        // ? の部分に学籍番号を入れて実行する
        $nameStmt->execute([$resetId]);
        // 結果の1列目（ユーザー名）を取り出す。見つからなければ false になる
        $targetName = $nameStmt->fetchColumn();

        if ($targetName === false) {
            $_SESSION['flash_message'] = 'ユーザーが見つかりません。';
            header('Location: user.php');
            exit();
        } else {
            // 仮パスワードと期限を作る。[ ] で、戻り値の2つをそれぞれの変数に受け取る
            [$plainPassword, $expires] = generateTempPassword();

            // DBのパスワード列を、仮パスワードで上書きする（※ハッシュ化なし）
            $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE student_number = ?");
            // ? の部分に仮パスワードと学籍番号を入れて実行する
            $stmt->execute([$plainPassword, $resetId]);
        }
        $_SESSION['flash_message'] = 'パスワードをリセットしました。';
        $_SESSION['temp_password'] = [
            'name' => $targetName,
            'password' => $plainPassword,
            'expires' => date('Y年m月d日 H時i分', $expires)
        ];
    }
    header('Location: user.php');
    exit();
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

    if ($name !== "管理者") {
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
// セッションに入っている仮パスワード情報を取り出す（なければ null）
$temp_password = $_SESSION['temp_password'] ?? null;
unset($_SESSION['flash_message']);
unset($_SESSION['temp_password']);
?>
<!DOCTYPE html>
<html lang="ja" class="user-management-page">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー管理 - e-reservation</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css?v=45">
</head>

<body class="profile-edit-page user-management-page">

    <script>
        function copyButton(elementId) {
            // 指定したIDの要素のテキストを取得
            var element = document.getElementById(elementId);

            // テキストをクリップボードにコピー
            navigator.clipboard.writeText(element.textContent);
        }
    </script>

    <header class="sub-header">
        <div class="sub-header-left">
            <a href="profile.php" class="back-btn-custom">‹</a>
            <h1 class="sub-header-title">ユーザー管理</h1>
        </div>
    </header>

    <main class="main-container user-main-container">

        <!-- ──【固定エリア】検索や並び替え部分 ── -->
        <div class="user-fixed-header-section">
            <?php if (!empty($flash_message)): ?>
                <div class="flash-message-box">
                    <?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($temp_password)): ?>
                <div class="temp-password-box">
                    <div class="temp-password-label">
                        <?php echo htmlspecialchars($temp_password['name'], ENT_QUOTES, 'UTF-8'); ?> さんの仮パスワード
                    </div>
                    <div class="temp-password-code">
                        <span id="copytxt">
                            <?php echo htmlspecialchars($temp_password['password'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <button class="user-search-btn" onclick="copyButton('copytxt')">コピー実行</button>
                    </div>
                    <div class="temp-password-note">
                        有効期限<?php echo htmlspecialchars($temp_password['expires'], ENT_QUOTES, 'UTF-8'); ?>まで<br>
                        この画面を離れると再表示できませんので、必ず控えておいてください。
                    </div>
                </div>
            <?php endif; ?>

            <form action="user.php" method="GET" class="user-search-form">
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="ユーザー名で検索..." class="user-search-input">
                <?php if ($sort !== 'new'): ?>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <button type="submit" class="user-search-btn">検索</button>
            </form>

            <div class="user-section-header" style="margin-bottom: 0;">
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
        </div>
        <!-- ── 固定エリアここまで ── -->

        <!-- ──【スクロールエリア】ユーザー一覧カード ── -->
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