<?php require 'header.php'; ?>
<?php
$host = 'localhost';
$port = '3306';
$dbname = 'e-reservation';  //各グループのデータベース名
$user = 'root';
$password = 'root';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $password);



$keyword = $_GET['keyword'] ?? '';

// 並び順を受け取る(指定がなければ asc = 昇順)
$order = $_GET['order'] ?? 'asc';
if ($order !== 'desc') {
    $order = 'asc';   // asc と desc 以外が来ても、asc にする
}

// ボタンを押したときに、次に切り替える並び順
$next_order = ($order === 'asc') ? 'desc' : 'asc';

// 画面に出す文字
$order_label = ($order === 'asc') ? '日時が近い順' : '日時が遠い順';

if ($keyword === '') {
    $stmt = $pdo->query("SELECT * FROM reservation ORDER BY scheduled_start_time {$order}");
} else {
    $stmt = $pdo->prepare(
        "SELECT DISTINCT r.*
        FROM reservation r
        JOIN user_reservation ur ON r.reservation_id = ur.reservation_id
        JOIN `user` u ON ur.student_number = u.student_number
        WHERE u.user_name LIKE ?
        ORDER BY r.scheduled_start_time {$order}"
    );
    $stmt->execute(['%' . $keyword . '%']);
}

$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約一覧</title>
    <meta name="description" content="">
    <link rel="stylesheet" href="css/hiroki.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="js/hiroki.js" defer></script>
</head>

<body class="list-page">

    <div class="header">
        <a class="new-account" href="javascript:history.back()">&lt; 予約一覧</a>
    </div>

    <div class="list-body">

        <!-- 検索欄 + 並び替えボタン -->
        <div class="search-area">
            <form action="#" class="search-form-3">
                <label>
                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="利用者名で検索">
                </label>
                <button type="submit" aria-label="検索"></button>
            </form>
            <a class="sort-btn" href="?<?= htmlspecialchars(http_build_query(['keyword' => $keyword, 'order' => $next_order])) ?>">並び替え</a>
        </div>

        <!-- 件数 と 並び順 -->
        <div class="list-info">
            <!-- 【DB】予約の件数(例: 2) -->
            <span>すべての予約 <?= count($reservations) ?>件</span>
            <span><?= $order_label ?></span>
        </div>

        <!-- =====================================================
            予約カード
            ▼ ここから「予約1件分」。DBの件数ぶん繰り返す(foreach)
            下の2枚は表示の見本。DBにつないだら1枚だけ残して繰り返す
            ===================================================== -->

        <?php foreach ($reservations as $row): ?>

            <?php
            $start = strtotime($row['scheduled_start_time']);   // DATETIME → 数値に変える
            // ゲーム: 予約の game_id を使って、game テーブルから1件取る
            $game = $pdo->query("SELECT * FROM game WHERE game_id = {$row['game_id']}")->fetch();

            // 席: seat_1〜seat_5 のうち、値が 1 の席の番号を集める
            $seats = [];
            for ($i = 1; $i <= 5; $i++) {
                if ($row['seat_' . $i] == 1) {
                    $seats[] = $i;
                }
            }


            // 活動中の判定
            if ($row["start_time"] == null && $row["end_time"] == null) {
                // 活動中ではない
                $status = "活動前";
                $badge = "res-badge-booked";
            } else if ($row["start_time"] != null && $row["end_time"] == null) {
                // 活動中
                $status = "活動中";
                $badge = "res-badge-active";
            } else if ($row["start_time"] != null && $row["end_time"] != null) {
                // 活動終了
                $status = "活動終了";
                $badge = "res-badge-ended";
            }
            // 取り出すのは、userテーブルの名前(user_name)だけ
            // --参加の記録のテーブルを使う。ur は短い呼び名
            // userテーブルをくっつける。u は短い呼び名(userは``で囲む)
            // 学生番号が同じ行どうしをくっつける(学生番号 → 名前がわかる)
            // その中から、今のカードの予約の分だけに絞る
            $members = $pdo->query(
                "SELECT u.user_name
                FROM user_reservation ur
                JOIN `user` u 
                ON ur.student_number = u.student_number
                WHERE ur.reservation_id = {$row['reservation_id']}"
            )->fetchAll(PDO::FETCH_COLUMN);
            ?>

            <a class="res-card" href="#">
                <div class="res-top">

                    <span class="res-date"><?= date('Y年n月j日', $start) ?></span>

                    <!-- 【DB】状態: res-badge-active(活動中) / res-badge-booked(予約済み) -->
                    <span class="res-badge <?= $badge ?>"><?= $status ?></span>
                </div>

                <!-- 【DB】時間: scheduled_start_time - scheduled_end_time -->
                <p class="res-time">
                    <?= date('H:i', $start) ?> - <?= date('H:i', strtotime($row['scheduled_end_time'])) ?>
                </p>

                <div class="res-game">
                    <!-- 【DB】ゲームアイコン: game.game_icon -->

                    <span class="res-icon">
                        <?php if ($game['game_icon'] != ''): ?>
                            <img src="<?= htmlspecialchars($game['game_icon']) ?>" alt="">
                        <?php else: ?>
                            🎮
                        <?php endif; ?>
                    </span>

                    <div class="res-text">
                        <!-- 【DB】ゲーム名: game.game_name -->
                        <p class="res-name"><?= htmlspecialchars($game['game_name']) ?></p>
                        <!-- 【DB】席: seat_1〜seat_5 / 人数: user_reservation の件数 -->
                        <p class="res-sub"><?= implode('・', $seats) ?>席 / <?= count($seats) ?>人</p>
                    </div>
                    <span class="res-arrow">&gt;</span>
                </div>

                <!-- 【DB】参加者名: user.user_name -->
                <p class="res-members"><?= implode('、', $members) ?></p>
            </a>
        <?php endforeach; ?>

        <!-- =====================================================
            予約カード ここまで
            ===================================================== -->
    </div>
</body>

</html>
<?php require 'footer.php'; ?>