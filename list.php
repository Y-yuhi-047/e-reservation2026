<?php require 'header.php'; ?>
<?php
$host = 'localhost';
$port = '3306';
$dbname = 'e-reservation';  //各グループのデータベース名
$user = 'root';
$password = 'root';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $password);

// --- プロフィール画面などから飛んできたかの判定 ---
$show_back = isset($_GET['from']) && $_GET['from'] === 'profile';

// --- ボタンが押されたときの処理（活動開始・終了・編集・削除） ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['reservation_id'])) {
    $targetId = $_POST['reservation_id'];

    if ($_POST['action'] === 'start') {
        $updateStmt = $pdo->prepare("UPDATE reservation SET start_time = NOW() WHERE reservation_id = ?");
        $updateStmt->execute([$targetId]);
    } elseif ($_POST['action'] === 'end') {
        $updateStmt = $pdo->prepare("UPDATE reservation SET end_time = NOW() WHERE reservation_id = ?");
        $updateStmt->execute([$targetId]);
    } elseif ($_POST['action'] === 'edit') {
        // datetime-local の形式（例: 2026-11-15T10:00）をデータベース用（2026-11-15 10:00:00）に変換
        $scheduledStartInput = $_POST['scheduled_start'] ?? '';
        $scheduledEndInput = $_POST['scheduled_end'] ?? '';

        $scheduledStart = str_replace('T', ' ', $scheduledStartInput) . ':00';
        $scheduledEnd = str_replace('T', ' ', $scheduledEndInput) . ':00';

        $gameId = !empty($_POST['game_id']) ? $_POST['game_id'] : null;

        $selectedSeats = $_POST['seats'] ?? [];
        $seat1 = in_array(1, $selectedSeats) ? 1 : 0;
        $seat2 = in_array(2, $selectedSeats) ? 1 : 0;
        $seat3 = in_array(3, $selectedSeats) ? 1 : 0;
        $seat4 = in_array(4, $selectedSeats) ? 1 : 0;
        $seat5 = in_array(5, $selectedSeats) ? 1 : 0;

        $updateStmt = $pdo->prepare(
            "UPDATE reservation 
             SET scheduled_start_time = ?, scheduled_end_time = ?, game_id = ?, 
                 seat_1 = ?, seat_2 = ?, seat_3 = ?, seat_4 = ?, seat_5 = ? 
             WHERE reservation_id = ?"
        );
        $updateStmt->execute([$scheduledStart, $scheduledEnd, $gameId, $seat1, $seat2, $seat3, $seat4, $seat5, $targetId]);

        $delStmt = $pdo->prepare("DELETE FROM user_reservation WHERE reservation_id = ?");
        $delStmt->execute([$targetId]);

        if (!empty($_POST['student_numbers'])) {
            $insStmt = $pdo->prepare("INSERT INTO user_reservation (reservation_id, student_number) VALUES (?, ?)");
            foreach ($_POST['student_numbers'] as $sNum) {
                $insStmt->execute([$targetId, $sNum]);
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $delUserStmt = $pdo->prepare("DELETE FROM user_reservation WHERE reservation_id = ?");
        $delUserStmt->execute([$targetId]);

        $delStmt = $pdo->prepare("DELETE FROM reservation WHERE reservation_id = ?");
        $delStmt->execute([$targetId]);
    }

    $redirect_url = "list.php" . ($show_back ? "?from=profile" : "");
    header("Location: " . $redirect_url);
    exit;
}

$gamesList = $pdo->query("SELECT * FROM game")->fetchAll(PDO::FETCH_ASSOC);
$allUsersList = $pdo->query("SELECT * FROM `user`")->fetchAll(PDO::FETCH_ASSOC);

$keyword = $_GET['keyword'] ?? '';
$order = $_GET['order'] ?? 'asc';
if ($order !== 'desc') {
    $order = 'asc';
}
$next_order = ($order === 'asc') ? 'desc' : 'asc';
$order_label = ($order === 'asc') ? '日時が近い順' : '日時が遠い順';

$query_params = ['keyword' => $keyword, 'order' => $next_order];
if ($show_back) {
    $query_params['from'] = 'profile';
}

if ($keyword === '') {
    $stmt = $pdo->query(
        "SELECT r.*, g.game_name, g.game_icon 
     FROM reservation r 
     LEFT JOIN game g ON r.game_id = g.game_id 
     ORDER BY r.scheduled_start_time {$order}"
    );
} else {
    $stmt = $pdo->prepare(
        "SELECT DISTINCT r.*, g.game_name, g.game_icon
         FROM reservation r
         LEFT JOIN game g ON r.game_id = g.game_id
         JOIN user_reservation ur ON r.reservation_id = ur.reservation_id
         JOIN `user` u ON ur.student_number = u.student_number
         WHERE u.user_name LIKE ?
         ORDER BY r.scheduled_start_time {$order}"
    );
    $stmt->execute(['%' . $keyword . '%']);
}

$reservations = $stmt->fetchAll();
$current_page = 'list';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約一覧</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="css/hiroki.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="js/hiroki.js" defer></script>
</head>

<body class="list-page">

    <div class="list-body">

        <!-- ── スクロールしても上に残るエリア ── -->
        <div class="sticky-top-wrapper">

            <div class="page-title-area">
                <?php if ($show_back): ?>
                    <a href="javascript:history.back()" class="back-link">&lt;</a>
                <?php endif; ?>
                <span>予約一覧</span>
            </div>

            <div class="search-area">
                <form action="list.php" class="search-form-3">
                    <?php if ($show_back): ?>
                        <input type="hidden" name="from" value="profile">
                    <?php endif; ?>
                    <label>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="利用者名で検索">
                    </label>
                    <button type="submit" aria-label="検索"></button>
                </form>
                <a class="sort-btn" href="?<?= htmlspecialchars(http_build_query($query_params)) ?>">並び替え</a>
            </div>

            <div class="list-info" style="margin-bottom: 0;">
                <span>すべての予約 <?= count($reservations) ?>件</span>
                <span>
                    <?php
                    // 「近い」「遠い」の文字を、カラー付きのHTMLタグに置き換える
                    $formatted_label = str_replace(
                        ['近い', '遠い'],
                        ['<span style="color: #44685d; font-weight: bold;">近い</span>', '<span style="color: #44685d; font-weight: bold;">遠い</span>'],
                        $order_label
                    );
                    echo $formatted_label;
                    ?>
                </span>
            </div>
        </div>

        <?php foreach ($reservations as $row): ?>

            <?php
            $start = strtotime($row['scheduled_start_time']);
            $end = strtotime($row['scheduled_end_time']);

            $seats = [];
            for ($i = 1; $i <= 5; $i++) {
                if ($row['seat_' . $i] == 1) {
                    $seats[] = $i;
                }
            }

            $status = "活動前";
            $badge = "res-badge-booked";

            if ($row["start_time"] == null && $row["end_time"] == null) {
                $status = "活動前";
                $badge = "res-badge-booked";
            } else if ($row["start_time"] != null && $row["end_time"] == null) {
                $status = "活動中";
                $badge = "res-badge-active";
            } else if ($row["start_time"] != null && $row["end_time"] != null) {
                $status = "活動終了";
                $badge = "res-badge-ended";
            }

            $membersStmt = $pdo->prepare(
                "SELECT u.student_number, u.user_name, u.profile_picture
                FROM user_reservation ur
                JOIN `user` u 
                ON ur.student_number = u.student_number
                WHERE ur.reservation_id = ?"
            );
            $membersStmt->execute([$row['reservation_id']]);
            $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
            $memberStudentNumbers = array_column($members, 'student_number');

            $gameName = $row['game_name'] !== null ? $row['game_name'] : '（削除されたゲーム）';
            $gameIcon = $row['game_icon'] ?? '';
            $memo = $row['memo'] ?? '';
            $selectedGame = null;
            foreach ($gamesList as $g) {
                if ($row['game_id'] == $g['game_id']) {
                    $selectedGame = $g;
                    break;
                }
            }

            // 曜日の日本語表記用配列
            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            $weekday = $weekdays[date('w', $start)];
            ?>

            <!-- カード本体 -->
            <a class="res-card" href="javascript:void(0);" onclick="openDetailModal(<?= $row['reservation_id'] ?>);">
                <div class="res-top">
                    <span class="res-date"><?= date('Y年n月j日', $start) ?></span>
                    <span class="res-badge <?= $badge ?>"><?= $status ?></span>
                </div>

                <p class="res-time">
                    <?= date('H:i', $start) ?> - <?= date('H:i', $end) ?>
                </p>

                <div class="res-game">
                    <span class="res-icon">
                        <?php if ($gameIcon !== ''): ?>
                            <img src="<?= htmlspecialchars($gameIcon) ?>" alt="">
                        <?php else: ?>
                            🎮
                        <?php endif; ?>
                    </span>

                    <div class="res-text">
                        <p class="res-name"><?= htmlspecialchars($gameName) ?></p>
                        <p class="res-sub"><?= implode('・', $seats) ?>席 / <?= count($seats) ?>人</p>
                    </div>
                    <span class="res-arrow">&gt;</span>
                </div>

                <p class="res-members">
                    <?php
                    $names = array_column($members, 'user_name');
                    echo implode('、', $names);
                    ?>
                </p>
            </a>

            <!-- ── 1. 予約詳細モーダル ── -->
            <div id="detail-modal-<?= $row['reservation_id'] ?>" class="modal-overlay" style="display: none;">
                <div class="modal-content">

                    <div class="modal-header">
                        <span class="modal-title">予約詳細</span>
                        <button type="button" class="modal-close-btn" onclick="closeDetailModal(<?= $row['reservation_id'] ?>);">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="card" style="margin-bottom: 16px; background-color: #ffffff; cursor: default;">
                        <div style="width: 100%;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 13px; color: #666;"><?= date('Y年n月j日', $start) ?>（<?= $weekday ?>）</span>
                                <span class="res-badge <?= $badge ?>" style="position: static;"><?= $status ?></span>
                            </div>
                            <div style="font-size: 20px; font-weight: bold; color: #1a1a1a; margin-bottom: 12px;">
                                <?= date('H:i', $start) ?> - <?= date('H:i', $end) ?>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; background: #e6f2ed; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    <?php if ($gameIcon !== ''): ?>
                                        <img src="<?= htmlspecialchars($gameIcon) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        🎮
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: bold; font-size: 14px; color: #1a1a1a;"><?= htmlspecialchars($gameName) ?></div>
                                    <div style="font-size: 12px; color: #888;"><?= implode('・', $seats) ?>席 / <?= count($seats) ?>人</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12px; font-weight: bold; color: #888888; margin-bottom: 8px;">メンバー</div>
                        <!-- ▼ クラス名を付与してスクロール可能にする -->
                        <div class="detail-member-list-box">
                            <?php foreach ($members as $mem): ?>
                                <div style="display: flex; align-items: center; gap: 10px; background: #ffffff; padding: 8px 12px; border-radius: 10px; border: 1px solid #f0f2f1;">
                                    <div style="width: 32px; height: 32px; background: #e8f2ee; color: #1b4d3e; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; overflow: hidden; flex-shrink: 0;">
                                        <?php if (!empty($mem['profile_picture'])): ?>
                                            <img src="<?= htmlspecialchars($mem['profile_picture']) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="fas fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span style="font-size: 14px; font-weight: bold; color: #1a1a1a;"><?= htmlspecialchars($mem['user_name']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 12px; font-weight: bold; color: #888888; margin-bottom: 6px;">メモ</div>
                        <div style="background: #ffffff; border: 1px solid #dce3e0; border-radius: 10px; padding: 12px; font-size: 13px; color: #333; min-height: 40px; box-sizing: border-box;">
                            <?= !empty($memo) ? nl2br(htmlspecialchars($memo)) : '<span style="color: #aaa;">メモはありません</span>' ?>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php if (empty($row['start_time'])): ?>
                            <form action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST" style="margin: 0;">
                                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                                <input type="hidden" name="action" value="start">
                                <button type="submit" class="save-btn" style="background-color: #1b4d3e; display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%;">
                                    <i class="fas fa-play"></i> 活動開始
                                </button>
                            </form>
                        <?php elseif (empty($row['end_time'])): ?>
                            <form action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST" style="margin: 0;">
                                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                                <input type="hidden" name="action" value="end">
                                <button type="submit" class="save-btn" style="background-color: #ffffff; color: #1b4d3e; border: 1px solid #1b4d3e; display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%;">
                                    <i class="fas fa-stop"></i> 終了
                                </button>
                            </form>
                        <?php else: ?>
                            <div style="text-align: center; color: #666; font-size: 13px; padding: 10px; background: #eaeded; border-radius: 8px;">
                                <i class="fas fa-check-circle" style="color: #1b4d3e;"></i> 活動は終了しました
                            </div>
                        <?php endif; ?>

                        <!-- ▼ 活動がまだ終了していない（end_time が空）のときだけ「予約編集」ボタンを表示する -->
                        <?php if (empty($row['end_time'])): ?>
                            <div style="display: flex; gap: 8px; margin-top: 4px;">
                                <button type="button" class="save-btn" style="flex: 1; background-color: #ffffff; color: #1b4d3e; border: 1px solid #dce3e0; font-size: 14px; padding: 12px;" onclick="openEditModal(<?= $row['reservation_id'] ?>);">
                                    <i class="fas fa-pen"></i> 予約編集
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <!-- ── 2. 予約編集モーダル ── -->
            <div id="edit-modal-<?= $row['reservation_id'] ?>" class="modal-overlay" style="display: none;">
                <div class="modal-content">

                    <div class="modal-header">
                        <span class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                            <span style="cursor: pointer; font-weight: bold;" onclick="closeEditModal(<?= $row['reservation_id'] ?>); openDetailModal(<?= $row['reservation_id'] ?>);">&lt;</span> 予約編集
                        </span>
                        <button type="button" class="modal-close-btn" onclick="closeEditModal(<?= $row['reservation_id'] ?>);">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST">
                        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                        <input type="hidden" name="action" value="edit">

                        <!-- 日付入力欄（見本に合わせた大きな角丸）
                        <div class="form-group">
                            <label class="form-label">日付</label>
                            <div class="custom-input-box" style="display: flex; align-items: center; justify-content: space-between; padding: 0 16px;">
                                <span style="font-size: 15px; color: #333; font-weight: 500;"><?= date('Y年n月j日', $start) ?>（<?= $weekday ?>）</span>
                                <input type="date" name="date" class="form-control-hidden-date" value="<?= date('Y-m-d', $start) ?>" required>
                                <i class="far fa-calendar-alt" style="font-size: 18px; color: #666; cursor: pointer;"></i>
                            </div>
                        </div> -->

                        <!-- 開始日時 -->
                        <div class="form-group">
                            <label class="form-label">開始日時</label>
                            <div class="custom-input-box" style="display: flex; align-items: center; padding: 0 16px; height: 48px;">
                                <input type="datetime-local" name="scheduled_start" value="<?= date('Y-m-d\TH:i', $start) ?>" required style="width: 100%; border: none; background: transparent; font-size: 15px; color: #333; outline: none;">
                            </div>
                        </div>

                        <!-- 終了日時 -->
                        <div class="form-group">
                            <label class="form-label">終了日時</label>
                            <div class="custom-input-box" style="display: flex; align-items: center; padding: 0 16px; height: 48px;">
                                <input type="datetime-local" name="scheduled_end" value="<?= date('Y-m-d\TH:i', $end) ?>" required style="width: 100%; border: none; background: transparent; font-size: 15px; color: #333; outline: none;">
                            </div>
                        </div>

                        <!-- ゲーム選択（リアルタイム連動版） -->
                        <div class="form-group">
                            <label class="form-label">ゲーム</label>
                            <div class="custom-game-box">
                                <div style="display: flex; align-items: center; gap: 10px; width: 100%; position: relative;">

                                    <!-- ▼ アイコンを表示するエリア（IDを付与） -->
                                    <div id="game-icon-preview-<?= $row['reservation_id'] ?>" style="width: 32px; height: 32px; background: #e6f2ed; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                        <?php if (!empty($gameIcon)): ?>
                                            <img src="<?= htmlspecialchars($gameIcon) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            🎮
                                        <?php endif; ?>
                                    </div>

                                    <!-- ▼ onchangeイベントと data-icon を追加 -->
                                    <select name="game_id" class="custom-game-select" onchange="updateGameIcon(this, <?= $row['reservation_id'] ?>)">
                                        <option value="">（ゲームなし）</option>
                                        <?php foreach ($gamesList as $g): ?>
                                            <option value="<?= $g['game_id'] ?>" data-icon="<?= htmlspecialchars($g['game_icon'] ?? '') ?>" <?= $row['game_id'] == $g['game_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['game_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 席を選択 -->
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="form-label" style="margin-bottom: 0;">席を選択</label>
                                <span class="seat-count-text" style="font-size: 12px; color: #1b4d3e; font-weight: bold;"><?= count($seats) ?>席 ・ <?= count($members) ?>人</span>
                            </div>
                            <div class="seat-group-box">
                                <div class="seat-container">
                                    <div class="seat-row">
                                        <label class="seat-chip">4<input type="checkbox" name="seats[]" value="4" class="seat-checkbox" <?= $row['seat_4'] == 1 ? 'checked' : '' ?>></label>
                                        <label class="seat-chip">5<input type="checkbox" name="seats[]" value="5" class="seat-checkbox" <?= $row['seat_5'] == 1 ? 'checked' : '' ?>></label>
                                    </div>
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <label class="seat-chip">3<input type="checkbox" name="seats[]" value="3" class="seat-checkbox" <?= $row['seat_3'] == 1 ? 'checked' : '' ?>></label>
                                        <label class="seat-chip">2<input type="checkbox" name="seats[]" value="2" class="seat-checkbox" <?= $row['seat_2'] == 1 ? 'checked' : '' ?>></label>
                                        <label class="seat-chip">1<input type="checkbox" name="seats[]" value="1" class="seat-checkbox" <?= $row['seat_1'] == 1 ? 'checked' : '' ?>></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- メンバー選択（アイコン付き） -->
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="form-label" style="margin-bottom: 0;">メンバー</label>
                                <span class="member-count-text" style="font-size: 12px; color: #1b4d3e; font-weight: bold;"><?= count($members) ?>人を選択中</span>
                            </div>
                            <div class="member-list-box">
                                <?php foreach ($allUsersList as $usr): ?>
                                    <label class="member-item" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <!-- チェックボックス -->
                                        <input type="checkbox" name="student_numbers[]" class="member-checkbox" value="<?= htmlspecialchars($usr['student_number']) ?>"
                                            <?= in_array($usr['student_number'], $memberStudentNumbers) ? 'checked' : '' ?>>

                                        <!-- ▼ プロフィール画像のアイコン（丸型プレビュー） -->
                                        <div style="width: 28px; height: 28px; background: #e6f2ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                            <?php if (!empty($usr['profile_picture'])): ?>
                                                <img src="<?= htmlspecialchars($usr['profile_picture']) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                            <?php else: ?>
                                                👤
                                            <?php endif; ?>
                                        </div>

                                        <!-- ユーザー名 -->
                                        <span style="font-weight: 500; color: #333;"><?= htmlspecialchars($usr['user_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- 保存・予約取り消しボタン（横並び） -->
                        <div style="display: flex; gap: 10px; margin-top: 24px;">
                            <button type="submit" class="btn-save" style="flex: 1;">保存</button>
                            <button type="submit" form="delete-form-<?= $row['reservation_id'] ?>" class="btn-cancel" style="flex: 1; color: #d9534f; border-color: #f5c6cb; background-color: #fdf2f2;" onclick="return confirm('本当にこの予約を取り消しますか？');">予約を取り消す</button>
                        </div>
                    </form>

                    <form id="delete-form-<?= $row['reservation_id'] ?>" action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST" style="display: none;">
                        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                        <input type="hidden" name="action" value="delete">
                    </form>

                </div>
            </div>

        <?php endforeach; ?>

    </div>

    <!-- モーダル開閉 & リアルタイム連動用JavaScript -->
    <script>
        function openDetailModal(id) {
            var modal = document.getElementById('detail-modal-' + id);
            if (modal) modal.style.display = 'flex';
        }

        function closeDetailModal(id) {
            var modal = document.getElementById('detail-modal-' + id);
            if (modal) modal.style.display = 'none';
        }

        function openEditModal(id) {
            closeDetailModal(id);
            var modal = document.getElementById('edit-modal-' + id);
            if (modal) modal.style.display = 'flex';
        }

        function closeEditModal(id) {
            var modal = document.getElementById('edit-modal-' + id);
            if (modal) modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }

        // 席数・人数のリアルタイム更新処理
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('seat-checkbox') || e.target.classList.contains('member-checkbox')) {
                var modal = e.target.closest('.modal-content');
                if (modal) {
                    var checkedSeats = modal.querySelectorAll('.seat-checkbox:checked').length;
                    var checkedMembers = modal.querySelectorAll('.member-checkbox:checked').length;

                    var seatCountText = modal.querySelector('.seat-count-text');
                    if (seatCountText) {
                        seatCountText.textContent = checkedSeats + '席 ・ ' + checkedMembers + '人';
                    }

                    var memberCountText = modal.querySelector('.member-count-text');
                    if (memberCountText) {
                        memberCountText.textContent = checkedMembers + '人を選択中';
                    }
                }
            }
        });
        // ゲームのセレクトボックスが変更されたときにアイコンをリアルタイムで切り替える関数
        function updateGameIcon(selectElement, reservationId) {
            var selectedOption = selectElement.options[selectElement.selectedIndex];
            var iconUrl = selectedOption.getAttribute('data-icon');
            var previewContainer = document.getElementById('game-icon-preview-' + reservationId);

            if (previewContainer) {
                if (iconUrl && iconUrl !== '') {
                    previewContainer.innerHTML = '<img src="' + iconUrl + '" alt="" style="width:100%; height:100%; object-fit:cover;">';
                } else {
                    previewContainer.innerHTML = '🎮';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="keyword"]');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    // 入力されたキーワード（小文字に統一し、前後の余白を削除）
                    const keyword = searchInput.value.toLowerCase().trim();

                    // 予約カードの要素をすべて取得（クラス名 .res-card を指定）
                    const reservationCards = document.querySelectorAll('.res-card');

                    reservationCards.forEach(card => {
                        // カード内のテキスト（日付、ゲーム名、メンバーの名前など全て）を取得
                        const cardText = card.textContent.toLowerCase();

                        // キーワードが空、またはカード内のテキストにキーワードが含まれている場合は表示
                        if (keyword === '' || cardText.includes(keyword)) {
                            card.style.display = ''; // 表示する
                        } else {
                            card.style.display = 'none'; // 一致しない場合は非表示にする
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>
<?php require 'footer.php'; ?>