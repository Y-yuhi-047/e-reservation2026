<?php require 'header.php'; ?>
<?php
$host = 'localhost';
$port = '3306';
$dbname = 'e-reservation';  //各グループのデータベース名
$user = 'root';
$password = 'root';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $password);

$sql = "SELECT r* g.game_name, g.game_icon FROM reservation r JOIN game g ON r.game_id = g.game_id";
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
        <a class="new-account" href="#">&lt; 予約一覧</a>
    </div>

    <div class="list-body">

        <!-- 検索欄 + 並び替えボタン -->
        <div class="search-area">
            <form action="#" class="search-form-3">
                <label>
                    <input type="text" placeholder="利用者名で検索">
                </label>
                <button type="submit" aria-label="検索"></button>
            </form>
            <button type="button" class="sort-btn">並び替え</button>
        </div>

        <!-- 件数 と 並び順 -->
        <div class="list-info">
            <!-- 【DB】予約の件数(例: 2) -->
            <span>すべての予約 2件</span>
            <span>日時が近い順</span>
        </div>

        <!-- =====================================================
            予約カード
            ▼ ここから「予約1件分」。DBの件数ぶん繰り返す(foreach)
            下の2枚は表示の見本。DBにつないだら1枚だけ残して繰り返す
            ===================================================== -->


        <?php foreach ($pdo->query('SELECT * FROM reservation') as $row): ?>

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
            ?>




            <a class="res-card" href="#">
                <div class="res-top">

                    <span class="res-date"><?= date('Y年n月j日', $start) ?></span>

                    <!-- 【DB】状態: res-badge-active(活動中) / res-badge-booked(予約済み) -->
                    <span class="res-badge res-badge-active">活動中</span>
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
                <p class="res-members">山田 太郎、田中 一郎、中村 健</p>
            </a>
        <?php endforeach; ?>

        <!-- =====================================================
            予約カード ここまで
            ===================================================== -->
    </div>

</body>

</html>

<?php require 'footer.php'; ?>