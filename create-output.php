<?php session_start() ?>
<?php require 'header.php'?>
<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=e-reservation;charset=utf8mb4',
    'root',
    'root',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
echo '<pre>';
print_r($_POST);
echo '</pre>';

// どの席が選ばれたのかを判定
$seats = [];
for($i=1;$i<6;$i++){
    // echo $_POST['seats'.$i];
    if(isset($_POST['seats'.$i])){
        echo $_POST['seats'.$i].'をデータベースに登録'."\n";
        $seats[$i] = 1;
    } else {
        echo $i.'番はない'."\n";
        $seats[$i] = 0;
    }
}
// データベースに登録
try{
// reservationテーブルに入れる内容と順番
// 予約id,開始予定日時、終了予定日時,開始日時、終了日時,ゲームid,席1,2,3,4,5,
// 予約作成者、予約編集者、予約作成日時、予約編集日時、メモ
$sql = $pdo->prepare('insert into reservation
(scheduled_start_time, scheduled_end_time, game_id,
seat_1, seat_2, seat_3, seat_4, seat_5,
creator, updated_by, creation_date_time, update_date_time, memo)
values(?,?,?,?,?,?,?,?,?,?,?,?,?)');
    
$sql->execute([$_POST['scheduled_start_time'],$_POST['scheduled_end_time'],
$_POST['game_id'],
$seats[1],
$seats[2],
$seats[3],
$seats[4],
$seats[5],
$_SESSION['user']['number'],$_SESSION['user']['number'],
date('Y-m-d H:i:s'),date('Y-m-d H:i:s'),$_POST['memo']]);

// user_reservationテーブルに入れる内容
// 予約id,学生番号
$sql = $pdo->prepare('insert into user_reservation 
(reservation_id,student_number)
values(?,?)');

// 新しく作られた予約の予約idを取得
$reservation = $pdo->query('
select reservation_id from reservation
order by reservation_id desc
limit 1
');
var_dump($reservation);

// $reservationをそのままでは使えないのでfetchで連想配列（?）にしてそこから取り出す
// そうすると使える
$reservation_id = $reservation->fetch(PDO::FETCH_ASSOC)["reservation_id"];

foreach($_POST['student_numbers'] as $row){
    $sql->execute([$reservation_id,$row]);
}

echo '登録しました';
} catch(PDOException $e) {
     echo 'エラー: ' . $e->getMessage();
}

?>
