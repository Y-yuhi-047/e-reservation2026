<?php
// エラーを表示する設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// データベース接続設定
$dsn = 'mysql:host=localhost;dbname=e-reservation;charset=utf8';
$username = 'root'; 
$password = 'root';    

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // reservation と game テーブルを結合して予約情報を取得
    $sql = "
        SELECT 
            r.reservation_id,
            DATE(r.scheduled_start_time) AS date,
            TIME_FORMAT(r.scheduled_start_time, '%H:%i') AS start,
            TIME_FORMAT(r.scheduled_end_time, '%H:%i') AS end,
            g.game_name AS title,
            r.seat_1, r.seat_2, r.seat_3, r.seat_4, r.seat_5
        FROM reservation r
        LEFT JOIN game g ON r.game_id = g.game_id
    ";
    
    $stmt = $pdo->query($sql);
    $rawReservations = $stmt->fetchAll();

    // データベースの `seat_1` ～ `seat_5` カラム（0か1）を展開
    $reservations = [];
    foreach ($rawReservations as $row) {
        for ($i = 1; $i <= 5; $i++) {
            if (isset($row["seat_{$i}"]) && $row["seat_{$i}"] == 1) {
                $reservations[] = [
                    'date' => $row['date'],
                    'start' => $row['start'],
                    'end' => $row['end'],
                    'title' => $row['title'],
                    'seat' => $i
                ];
            }
        }
    }

} catch (PDOException $e) {
    $reservations = []; 
}
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>予約システム</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel='stylesheet' href="css/yuhi.css">
        <!-- カレンダーのデザイン -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.18.0/dist/event-calendar.min.css">
        
        <!-- 1. イベントカレンダー本体 -->
        <script src="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.18.0/dist/event-calendar.min.js"></script>

        <!-- 2. PHPからデータを渡すインラインスクリプト -->
        <script>
            window.appReservations = <?php echo json_encode($reservations, JSON_UNESCAPED_UNICODE); ?>;
            console.log("読み込んだ予約データ:", window.appReservations);
        </script>
        
        <!-- 3. 外部のscript.jsを読み込む -->
        <script src="js/script.js?v=2" defer></script>

        <!-- 予約を反映するために自動再読みする -->
        <!-- <meta http-equiv="refresh" content="3"> -->
    </head>
    <body>
        <?php require 'header.php'?>
        <div class="wrapper">
            
            <!-- 月間カレンダーの表示場所 -->
            <div id="calendar"></div>

            <!-- 選択した日付の表示 -->
            <h3 id="selected-date">日付を選択してください</h3>

            <!-- 選択した日の予約タイムライン -->
            <div id="reservation-timeline"></div>
            <div class="wrapper">

            <!-- ▼ このエリアを追加してください -->
            <div id="new-reservation-area" style="margin-top: 20px; text-align: center;"></div>

            <?php require 'footer.php'?>
        </div>
    </body>
</html>