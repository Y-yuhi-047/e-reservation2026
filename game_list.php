<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// セッションにゲームリストがなければ空の配列で初期化
if (!isset($_SESSION['games'])) {
    $_SESSION['games'] = [];
}

// POST送信された場合の処理（追加・削除）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. ゲーム追加処理
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $gameName = trim($_POST['game_name'] ?? '');
        $imagePath = '';

        if (isset($_FILES['game_image']) && $_FILES['game_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['game_image']['tmp_name'];
            $fileName = $_FILES['game_image']['name'];
            
            $uploadFileDir = 'uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $newFileName = 'game_' . md5(time() . $fileName) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagePath = $dest_path;
            }
        }

        if ($gameName !== '') {
            $newId = count($_SESSION['games']) > 0 ? max(array_column($_SESSION['games'], 'id')) + 1 : 1;
            $_SESSION['games'][] = [
                'id' => $newId, 
                'name' => $gameName, 
                'image' => $imagePath
            ];
        }
        header('Location: game_list.php');
        exit();
    }
    
    // 2. ゲーム削除処理
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deleteId = intval($_POST['game_id'] ?? 0);
        $_SESSION['games'] = array_values(array_filter($_SESSION['games'], function($g) use ($deleteId) {
            return $g['id'] !== $deleteId;
        }));
        header('Location: game_list.php');
        exit();
    }
}

$games = $_SESSION['games'];
$current_page = 'profile';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ゲーム管理 - e-reservation</title>
    
    <!-- アイコン用 Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- 共通CSS -->
    <link rel="stylesheet" href="css/style2.css?v=25">
</head>
<body class="profile-edit-page">

    <!-- ヘッダー -->
    <header class="sub-header">
        <div class="sub-header-left">
            <a href="profile.php" class="back-btn-custom">‹</a>
            <h1 class="sub-header-title">ゲーム管理</h1>
        </div>
        <button type="button" onclick="openGameModal();" class="add-game-circle-btn" title="ゲームを追加">
            <i class="fas fa-plus"></i>
        </button>
    </header>

    <!-- メインコンテンツ -->
    <main class="main-container" style="padding-bottom: 90px;">
        
        <!-- セクションタイトルと件数 -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 0 4px;">
            <span style="font-size: 13px; font-weight: bold; color: #888888;">登録ゲーム</span>
            <span style="font-size: 13px; color: #888888;"><?php echo count($games); ?>件</span>
        </div>

        <!-- ゲームリストを囲むカード -->
        <div class="card" style="display: flex; flex-direction: column; padding: 0; overflow: hidden; margin-bottom: 16px;">
            <?php if (empty($games)): ?>
                <div style="padding: 24px 16px; text-align: center; color: #888888; font-size: 14px; width: 100%; box-sizing: border-box;">
                    登録されているゲームはありません。
                </div>
            <?php else: ?>
                <?php foreach ($games as $game): ?>
                    <div class="game-item-row">
                        <!-- 左側：アイコン ＋ ゲーム名 -->
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div class="menu-icon green-bg" style="width: 40px; height: 40px; background-color: #e8f2ee; color: #1b4d3e; border-radius: 10px; display: flex; justify-content: center; align-items: center; font-size: 18px; overflow: hidden; flex-shrink: 0;">
                                <?php if (!empty($game['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($game['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-gamepad"></i>
                                <?php endif; ?>
                            </div>
                            <span style="font-size: 15px; font-weight: bold; color: #1a1a1a;">
                                <?php echo htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <!-- 右側：削除ボタン -->
                        <form action="game_list.php" method="POST" onsubmit="return confirm('本当に削除しますか？');" style="margin: 0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="game_id" value="<?php echo $game['id']; ?>">
                            <button type="submit" class="game-delete-btn">削除</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 下部の注意書き -->
        <div style="display: flex; align-items: center; gap: 6px; padding: 0 4px; color: #888888; font-size: 12px;">
            <i class="fas fa-info-circle"></i>
            <span>右上の＋からゲームを追加できます。</span>
        </div>

    </main>

    <!-- ── ゲーム追加用モーダル ── -->
    <div id="game-add-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">ゲームを追加</span>
                <button type="button" class="modal-close-btn" onclick="closeGameModal();"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="game_list.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <!-- アイコン選択エリア -->
                <div class="edit-avatar-section" style="margin: 16px 0 20px 0;">
                    <label for="modal-game-image-input" class="edit-avatar-wrapper" style="cursor: pointer;">
                        <div class="large-avatar" style="width: 75px; height: 75px; border-radius: 14px;">
                            <i id="modal-game-icon" class="fas fa-gamepad" style="font-size: 30px;"></i>
                            <img id="modal-game-preview" src="" alt="" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 14px;">
                        </div>
                        <div class="camera-badge" style="width: 26px; height: 26px; font-size: 11px;">
                            <i class="fas fa-camera"></i>
                        </div>
                    </label>
                    <input type="file" id="modal-game-image-input" name="game_image" accept="image/*" style="display: none;" onchange="previewGameImage(this);">
                    <label for="modal-game-image-input" class="add-icon-text" style="cursor: pointer; font-size: 12px; margin-top: 6px;">アイコン画像を選択</label>
                </div>

                <!-- ゲーム名入力欄 -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="game_name" class="form-label">ゲーム名</label>
                    <input type="text" id="game_name" name="game_name" placeholder="例: VALORANT" required class="form-input" autocomplete="off">
                </div>

                <button type="submit" class="save-btn">追加する</button>
            </form>
        </div>
    </div>


    <!-- モーダル開閉・画像プレビュー用JavaScript -->
    <script>
    function openGameModal() {
        var modal = document.getElementById('game-add-modal');
        if (modal) {
            modal.style.display = 'flex';
            var input = document.getElementById('game_name');
            if (input) {
                input.focus();
            }
        }
    }

    function closeGameModal() {
        var modal = document.getElementById('game-add-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById('game-add-modal');
        if (event.target === modal) {
            closeGameModal();
        }
    }

    function previewGameImage(obj) {
        if (obj.files && obj.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('modal-game-preview');
                var icon = document.getElementById('modal-game-icon');

                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (icon) {
                    icon.style.display = 'none';
                }
            }
            reader.readAsDataURL(obj.files[0]);
        }
    }
    </script>

</body>
</html>