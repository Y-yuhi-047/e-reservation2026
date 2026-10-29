<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- データベース接続設定 ---
$host = 'localhost';
$dbname = 'e-reservation';
$username = 'root';
$password = 'root'; // ※パスワードが設定されていない場合は '' に変更してください

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('データベース接続エラー: ' . $e->getMessage());
}

// 【重要】テスト用に、ログイン中の学籍番号を強制的に 'A2301001' に指定（ログイン機能が未実装の場合の対策）
if (!isset($_SESSION['user']['student_number'])) {
    $_SESSION['user']['student_number'] = 'A2301001';
}
$loginStudentNumber = $_SESSION['user']['student_number'];

// データベースから最新のユーザー情報を取得
$stmt = $pdo->prepare("SELECT * FROM user WHERE student_number = ?");
$stmt->execute([$loginStudentNumber]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

// ユーザーが見つからない場合のフォールバック
if (!$currentUser) {
    $currentUser = [
        'student_number' => $loginStudentNumber,
        'user_name' => 'テストユーザー',
        'profile_picture' => ''
    ];
}

$username = $currentUser['user_name'];
$userAvatar = $currentUser['profile_picture'];
$current_page = 'profile';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プロフィール - e-reservation</title>
    <!-- アイコン用 Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 共通CSS -->
    <link rel="stylesheet" href="css/style2.css">
</head>
<body class="profile-page">

    <!-- 共通ヘッダー -->
    <?php require 'header.php'; ?>

    <main class="main-container">
        <!-- 1. ユーザー情報カード：クリックで編集モーダルが開きます -->
        <div class="card user-card user-card-link" onclick="openModal();" style="cursor: pointer;">
            <div class="user-info">
                <div class="avatar-icon">
                    <i id="profile-avatar-icon" class="fas fa-user" style="<?php echo !empty($userAvatar) ? 'display: none;' : ''; ?>"></i>
                    <img id="profile-avatar-preview" src="<?php echo htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="ユーザーアイコン" style="<?php echo empty($userAvatar) ? 'display: none;' : ''; ?> width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <span class="edit-link">プロフィール編集</span>
                </div>
            </div>
            <i class="fas fa-chevron-right arrow-icon"></i>
        </div>

        <!-- 2. 自分の予約メニュー -->
        <a href="list.php" class="card simple-menu-item" style="text-decoration: none; color: inherit;">
            <div class="menu-left">
                <div class="menu-icon green-bg">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="menu-text">自分の予約</span>
            </div>
            <i class="fas fa-chevron-right arrow-icon"></i>
        </a>

        <!-- 3. ログアウトボタン -->
        <a href="logout.php" class="logout-btn" onclick="return confirm('本当にログアウトしますか？');" style="text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i>
            <span>ログアウト</span>
        </a>

        <!-- 4. 管理者用セクション（学籍番号がMで始まらない場合は管理者メニューを表示） -->
        <?php 
            $isAdmin = !str_starts_with($currentUser['student_number'], 'M');
        ?>
        <?php if ($isAdmin): ?>
            <div class="section-title">管理者用</div>
            <div class="card-group">
                <a href="user.php" class="card menu-card inner-card" style="text-decoration: none; color: inherit;">
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
                </a>

                <a href="game_list.php" class="card menu-card inner-card" style="text-decoration: none; color: inherit;">
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
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- ── プロフィール編集モーダル ── -->
    <div id="profile-edit-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">プロフィール編集</span>
                <button type="button" class="modal-close-btn" onclick="closeModal();"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="profile_update.php" method="POST" enctype="multipart/form-data">
                <!-- アイコン編集エリア -->
                <div class="edit-avatar-section">
                    <label for="modal-avatar-input" class="edit-avatar-wrapper" style="cursor: pointer;">
                        <div class="large-avatar">
                            <i id="modal-avatar-icon" class="fas fa-user" style="<?php echo !empty($userAvatar) ? 'display: none;' : ''; ?>"></i>
                            <img id="modal-avatar-preview" src="<?php echo htmlspecialchars($userAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="ユーザーアイコン" style="<?php echo empty($userAvatar) ? 'display: none;' : ''; ?> width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="camera-badge">
                            <i class="fas fa-camera"></i>
                        </div>
                    </label>
                    <input type="file" id="modal-avatar-input" name="avatar" accept="image/*" style="display: none;" onchange="previewModalEditImage(this);">
                    <label for="modal-avatar-input" class="add-icon-text" style="cursor: pointer;">アイコンを追加</label>
                </div>

                <!-- ユーザー名入力欄 -->
                <div class="form-group">
                    <label for="modal-username" class="form-label">ユーザー名</label>
                    <input type="text" id="modal-username" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" class="form-input">
                    <span class="form-hint">予約や利用者一覧に表示する名前です。</span>
                </div>

                <!-- 保存ボタン -->
                <button type="submit" class="save-btn">変更を保存</button>
            </form>
        </div>
    </div>

    <!-- 共通フッター -->
    <?php require 'footer.php'; ?>

    <!-- モーダル開閉・画像プレビュー用JavaScript -->
    <script>
    function openModal() {
        document.getElementById('profile-edit-modal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('profile-edit-modal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById('profile-edit-modal');
        if (event.target === modal) {
            closeModal();
        }
    }

    function previewModalEditImage(obj) {
        if (obj.files && obj.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var modalPreview = document.getElementById('modal-avatar-preview');
                var modalIcon = document.getElementById('modal-avatar-icon');
                var pagePreview = document.getElementById('profile-avatar-preview');
                var pageIcon = document.getElementById('profile-avatar-icon');

                modalPreview.src = e.target.result;
                modalPreview.style.display = 'block';
                if (modalIcon) modalIcon.style.display = 'none';

                if (pagePreview) {
                    pagePreview.src = e.target.result;
                    pagePreview.style.display = 'block';
                }
                if (pageIcon) pageIcon.style.display = 'none';
            }
            reader.readAsDataURL(obj.files[0]);
        }
    }
    </script>
</body>
</html>