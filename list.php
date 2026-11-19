<?php require 'header.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
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

// ==========================================
// 【自動終了処理】20:00過ぎ＆未終了のものを強制終了
// ==========================================
$now = time();
$autoEndStmt = $pdo->query("SELECT reservation_id, scheduled_start_time FROM reservation WHERE start_time IS NOT NULL AND (end_time IS NULL OR end_time = '')");
$activeReservations = $autoEndStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($activeReservations as $res) {
    $targetDate = date('Y-m-d', strtotime($res['scheduled_start_time']));
    $deadlineTimestamp = strtotime($targetDate . ' 20:00:00');

    if ($now > $deadlineTimestamp) {
        $forceEndStmt = $pdo->prepare("UPDATE reservation SET end_time = ? WHERE reservation_id = ?");
        $forceEndStmt->execute([$targetDate . ' 20:00:00', $res['reservation_id']]);
    }
}
// ==========================================

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
                seat_1 = ?, seat_2 = ?, seat_3 = ?, seat_4 = ?, seat_5 = ?,
                updated_by = ?
            WHERE reservation_id = ?"
        );
        $updateStmt->execute([
            $scheduledStart,
            $scheduledEnd,
            $gameId,
            $seat1,
            $seat2,
            $seat3,
            $seat4,
            $seat5,
            $_SESSION['user']['number'],
            $targetId
        ]);

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
$userNames = array_column($allUsersList, 'user_name', 'student_number');

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
$today = date('Y-m-d');
$upcoming = [];
$past = [];

foreach ($reservations as $r) {
    if (date('Y-m-d', strtotime($r['scheduled_start_time'])) < $today) {
        $past[] = $r;
    } else {
        $upcoming[] = $r;
    }
}

$reservations = array_merge($upcoming, $past);
$pastCount = count($past);
$pastStarted = false;
$current_page = 'list';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約一覧 - e-reservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/hiroki.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="js/hiroki.js" defer></script>
</head>

<body class="list-page">

    <div class="list-body">

        <!-- ── スクロールしても上に残るエリア ── -->
        <div class="sticky-top-wrapper">
            <div class="search-area">
                <form action="list.php" class="search-form-3">
                    <?php if ($show_back): ?>
                        <input type="hidden" name="from" value="profile">
                    <?php endif; ?>
                    <label>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="キーワードで検索">
                    </label>
                    <button type="submit" aria-label="検索"></button>
                </form>
                <a class="sort-btn" href="?<?= htmlspecialchars(http_build_query($query_params)) ?>">並び替え</a>
            </div>

            <div class="list-info">
                <span>すべての予約 <?= count($reservations) ?>件</span>
                <span>
                    <?php
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
            $isPast = date('Y-m-d', strtotime($row['scheduled_start_time'])) < $today;

            if ($isPast && !$pastStarted) {
                $pastStarted = true;
                $openAttr = ($keyword !== '') ? ' open' : '';
                echo '<details class="past-group"><summary>過去の予約（' . $pastCount . '件）</summary><div class="past-list">';
            }
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

            $myNumber = (string)($_SESSION['user']['number'] ?? '');
            $isMember = in_array($myNumber, $memberStudentNumbers, true);
            $gameName = $row['game_name'] !== null ? $row['game_name'] : '未選択';
            $gameIcon = $row['game_icon'] ?? '';
            $memo = $row['memo'] ?? '';
            $selectedGame = null;
            foreach ($gamesList as $g) {
                if ($row['game_id'] == $g['game_id']) {
                    $selectedGame = $g;
                    break;
                }
            }

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

                    <div class="card detail-card">
                        <div class="detail-card-inner">
                            <div class="detail-card-top">
                                <span class="detail-card-date"><?= date('Y年n月j日', $start) ?>（<?= $weekday ?>）<br>
                                    最終更新：<?= htmlspecialchars($userNames[$row['updated_by']] ?? $row['updated_by']) ?>
                                </span>
                                <span class="res-badge <?= $badge ?> detail-badge-static"><?= $status ?></span>
                            </div>
                            <div class="detail-card-time">
                                <?= date('H:i', $start) ?> - <?= date('H:i', $end) ?>
                            </div>
                            <div class="detail-game-row">
                                <div class="detail-game-icon-box">
                                    <?php if ($gameIcon !== ''): ?>
                                        <img src="<?= htmlspecialchars($gameIcon) ?>" alt="" class="detail-img-fit">
                                    <?php else: ?>
                                        🎮
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="detail-game-name"><?= htmlspecialchars($gameName) ?></div>
                                    <div class="detail-game-sub"><?= implode('・', $seats) ?>席 / <?= count($seats) ?>人</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-label">メンバー</div>
                        <div class="detail-member-list-box">
                            <?php foreach ($members as $mem): ?>
                                <div class="detail-member-item">
                                    <div class="detail-avatar-icon">
                                        <?php if (!empty($mem['profile_picture'])): ?>
                                            <img src="<?= htmlspecialchars($mem['profile_picture']) ?>" alt="" class="detail-img-fit">
                                        <?php else: ?>
                                            <i class="fas fa-user"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span class="detail-member-name"><?= htmlspecialchars($mem['user_name']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="detail-section-memo">
                        <div class="detail-label">メモ</div>
                        <div class="detail-memo-box">
                            <?= !empty($memo) ? nl2br(htmlspecialchars($memo)) : '<span class="detail-memo-empty">メモはありません</span>' ?>
                        </div>
                    </div>

                    <div class="detail-action-col">
                        <?php if (empty($row['start_time'])): ?>
                            <form action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST" class="detail-form-margin">
                                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                                <input type="hidden" name="action" value="start">
                                <?php if (empty($row['end_time']) && $isMember || $_SESSION['user']['name'] === "管理者"): ?>
                                    <button type="submit" class="save-btn detail-btn-start">
                                        <i class="fas fa-play"></i> 活動開始
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php elseif (empty($row['end_time'])): ?>
                            <form action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST" class="detail-form-margin">
                                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                                <input type="hidden" name="action" value="end">

                                <?php if (empty($row['end_time']) && $isMember || $_SESSION['user']['name'] === "管理者"): ?>
                                    <button type="submit" class="save-btn detail-btn-end">
                                        <i class="fas fa-stop"></i> 終了
                                    </button>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <div class="activity-ended-banner">
                                <i class="fas fa-check-circle" style="color: #1b4d3e;"></i> 活動は終了しました
                            </div>
                        <?php endif; ?>

                        <?php if (empty($row['end_time']) && $isMember || $_SESSION['user']['name'] === "管理者"): ?>
                            <div class="detail-edit-row">
                                <button type="button" class="save-btn detail-btn-to-edit" onclick="openEditModal(<?= $row['reservation_id'] ?>);">
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
                        <span class="modal-title edit-header-title">
                            <span class="edit-back-arrow" onclick="closeEditModal(<?= $row['reservation_id'] ?>); openDetailModal(<?= $row['reservation_id'] ?>);">&lt;</span> 予約編集
                        </span>
                        <button type="button" class="modal-close-btn" onclick="closeEditModal(<?= $row['reservation_id'] ?>);">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST">
                        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                        <input type="hidden" name="action" value="edit">

                        <div class="form-group">
                            <label class="form-label">開始日時</label>
                            <div class="custom-input-box" style="display: flex; align-items: center; padding: 0 16px; height: 48px;">
                                <input type="datetime-local" name="scheduled_start" step="1800" value="<?= date('Y-m-d\TH:i', $start) ?>" required style="width: 100%; border: none; background: transparent; font-size: 15px; color: #333; outline: none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">終了日時</label>
                            <div class="custom-input-box" style="display: flex; align-items: center; padding: 0 16px; height: 48px;">
                                <input type="datetime-local" name="scheduled_end" step="1800" value="<?= date('Y-m-d\TH:i', $end) ?>" required style="width: 100%; border: none; background: transparent; font-size: 15px; color: #333; outline: none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ゲーム</label>
                            <div class="custom-game-dropdown-wrapper edit-game-wrapper">
                                <div onclick="toggleCustomGameDropdown(event, <?= $row['reservation_id'] ?>)" class="edit-game-trigger">
                                    <div class="edit-game-trigger-inner">
                                        <div id="game-icon-preview-<?= $row['reservation_id'] ?>" class="edit-game-preview-icon">
                                            <?php if (!empty($gameIcon)): ?>
                                                <img src="<?= htmlspecialchars($gameIcon) ?>" alt="" class="detail-img-fit">
                                            <?php else: ?>
                                                <i class="fas fa-gamepad edit-gamepad-size"></i>
                                            <?php endif; ?>
                                        </div>
                                        <span id="game-name-preview-<?= $row['reservation_id'] ?>" class="edit-game-preview-name"><?= htmlspecialchars($gameName) ?></span>
                                    </div>
                                    <i class="fas fa-chevron-down edit-chevron"></i>
                                </div>

                                <input type="hidden" name="game_id" id="selected-game-id-<?= $row['reservation_id'] ?>" value="<?= htmlspecialchars($row['game_id'] ?? '') ?>">

                                <div id="game-dropdown-list-<?= $row['reservation_id'] ?>" class="custom-game-dropdown-list edit-dropdown-popup">
                                    <div onclick="selectCustomGame(event, <?= $row['reservation_id'] ?>, '', '未選択', '')" class="edit-dropdown-item edit-dropdown-item-border" onmouseover="this.style.background='#f7f9f8'" onmouseout="this.style.background='#fff'">
                                        <div class="edit-dropdown-ban-box">
                                            <i class="fas fa-ban edit-ban-icon"></i>
                                        </div>
                                        <span class="edit-dropdown-text">未選択</span>
                                    </div>
                                    <?php foreach ($gamesList as $g): ?>
                                        <div onclick="selectCustomGame(event, <?= $row['reservation_id'] ?>, '<?= $g['game_id'] ?>', '<?= htmlspecialchars($g['game_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($g['game_icon'] ?? '', ENT_QUOTES) ?>')" class="edit-dropdown-item edit-dropdown-item-border edit-dropdown-transition" onmouseover="this.style.background='#f7f9f8'" onmouseout="this.style.background='#fff'">
                                            <div class="edit-dropdown-icon-box">
                                                <?php if (!empty($g['game_icon'])): ?>
                                                    <img src="<?= htmlspecialchars($g['game_icon']) ?>" alt="" class="detail-img-fit">
                                                <?php else: ?>
                                                    <i class="fas fa-gamepad edit-gamepad-size"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span class="edit-dropdown-name"><?= htmlspecialchars($g['game_name']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="edit-label-row">
                                <label class="form-label edit-label-reset">席を選択</label>
                                <span class="seat-count-text edit-count-color"><?= count($seats) ?>席 ・ <?= count($members) ?>人</span>
                            </div>
                            <div class="seat-group-box">
                                <div class="seat-container">
                                    <div class="seat-row">
                                        <label class="seat-chip">4<input type="checkbox" name="seats[]" value="4" class="seat-checkbox" <?= $row['seat_4'] == 1 ? 'checked' : '' ?>></label>
                                        <label class="seat-chip">5<input type="checkbox" name="seats[]" value="5" class="seat-checkbox" <?= $row['seat_5'] == 1 ? 'checked' : '' ?>></label>
                                    </div>
                                    <div class="seat-row-center">
                                        <label class="seat-chip">3<input type="checkbox" name="seats[]" value="3" class="seat-checkbox" <?= $row['seat_3'] == 1 ? 'checked' : '' ?>></label>
                                        <label class="seat-chip">2<input type="checkbox" name="seats[]" value="2" class="seat-checkbox" <?= $row['seat_2'] == 1 ? 'checked' : '' ?>></label>
                                        <label class="seat-chip">1<input type="checkbox" name="seats[]" value="1" class="seat-checkbox" <?= $row['seat_1'] == 1 ? 'checked' : '' ?>></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="edit-label-row">
                                <label class="form-label edit-label-reset">メンバー</label>
                                <span class="member-count-text edit-count-color"><?= count($members) ?>人を選択中</span>
                            </div>

                            <div class="edit-member-bar">
                                <div class="sub-avatar-preview-list edit-preview-flex">
                                    <?php foreach ($members as $mem): ?>
                                        <div class="edit-avatar-col" title="<?= htmlspecialchars($mem['user_name']) ?>">
                                            <div class="edit-avatar-circle">
                                                <?php if (!empty($mem['profile_picture'])): ?>
                                                    <img src="<?= htmlspecialchars($mem['profile_picture']) ?>" alt="" class="detail-img-fit">
                                                <?php else: ?>
                                                    <i class="fas fa-user edit-user-icon-size"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span class="edit-avatar-name"><?= htmlspecialchars($mem['user_name']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- メンバー追加・変更ボタン（…） -->
                                <div onclick="openMemberSubModal(<?= $row['reservation_id'] ?>)" class="edit-dots-btn" title="メンバーを追加・変更">
                                    …
                                </div>
                            </div>
                        </div>

                        <label class="form-label">メモ</label>
                        <textarea name="memo" class="form-input edit-textarea-style" rows="2" placeholder="メモはありません"><?= (htmlspecialchars($memo)) ?></textarea>

                        <!-- ▼ 利用者一覧サブモーダル -->
                        <div id="sub-member-modal-<?= $row['reservation_id'] ?>" class="modal-overlay edit-submodal-overlay" style="display: none;">
                            <div class="modal-content edit-submodal-content">

                                <div class="edit-submodal-header-row">
                                    <button type="button" onclick="closeMemberSubModal(<?= $row['reservation_id'] ?>)" class="edit-submodal-back-btn">‹</button>
                                    <h2 class="edit-submodal-title">利用者一覧</h2>
                                </div>

                                <div class="edit-submodal-search-box">
                                    <i class="fas fa-search edit-submodal-search-icon"></i>
                                    <input type="text" class="sub-modal-search edit-submodal-input-reset" placeholder="利用者名で検索" oninput="filterSubModalMembers(this, <?= $row['reservation_id'] ?>)" />
                                </div>

                                <div class="edit-submodal-preview-box">
                                    <div class="edit-submodal-preview-info">
                                        <span>メンバー</span>
                                        <span class="sub-selected-count-label edit-count-color"><?= count($members) ?>人を選択中</span>
                                    </div>
                                    <div class="sub-avatar-preview-list edit-submodal-avatar-scroll"></div>
                                </div>

                                <div class="edit-submodal-list-scroll">
                                    <?php foreach ($allUsersList as $usr): ?>
                                        <?php $isChecked = in_array($usr['student_number'], $memberStudentNumbers); ?>
                                        <div class="sub-member-row <?= $isChecked ? 'checked' : '' ?>" data-name="<?= htmlspecialchars($usr['user_name']) ?>" onclick="toggleSubMemberItem(this, <?= $row['reservation_id'] ?>)">
                                            <div class="edit-sub-row-left">
                                                <div class="edit-sub-user-avatar">
                                                    <?php if (!empty($usr['profile_picture'])): ?>
                                                        <img src="<?= htmlspecialchars($usr['profile_picture']) ?>" alt="" class="detail-img-fit">
                                                    <?php else: ?>
                                                        <i class="fas fa-user edit-user-default-icon"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="edit-sub-username"><?= htmlspecialchars($usr['user_name']) ?></span>
                                            </div>

                                            <input type="checkbox" name="student_numbers[]" class="member-checkbox sub-member-chk" value="<?= htmlspecialchars($usr['student_number']) ?>" data-name="<?= htmlspecialchars($usr['user_name']) ?>" data-picture="<?= htmlspecialchars($usr['profile_picture'] ?? '') ?>" <?= $isChecked ? 'checked' : '' ?> style="display: none;">

                                            <div class="sub-select-icon">
                                                ✓
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="edit-submodal-footer">
                                    <button type="button" class="save-btn edit-submodal-ok-btn" onclick="closeMemberSubModal(<?= $row['reservation_id'] ?>)">
                                        決定する
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="edit-actions-row">
                            <button type="submit" class="btn-save edit-btn-flex">保存</button>
                            <button type="submit" form="delete-form-<?= $row['reservation_id'] ?>" class="btn-cancel edit-btn-cancel-custom" onclick="return confirm('本当にこの予約を取り消しますか？');">予約を取り消す</button>
                        </div>
                    </form>

                    <form id="delete-form-<?= $row['reservation_id'] ?>" action="list.php<?= $show_back ? '?from=profile' : '' ?>" method="POST" class="edit-hidden-form">
                        <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                        <input type="hidden" name="action" value="delete">
                    </form>

                </div>
            </div>

        <?php endforeach; ?>
        <?php
        if ($pastStarted) echo '</div></details>';
        ?>
    </div>


</body>

</html>
<?php require 'footer.php'; ?>