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
// セッションと$POSTの中身確認用
// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

// セッション情報があるか確認
$no_session = false;
if($_SESSION == null){
    $no_session = true;
}

// どの席が選ばれたのかを判定
$seats = [];
for($i=1;$i<6;$i++){
    // echo $_POST['seats'.$i];
    if(isset($_POST['seats'.$i])){
        // echo $_POST['seats'.$i].'をデータベースに登録'."\n";
        $seats[$i] = 1;
    } else {
        // echo $i.'番はない'."\n";
        $seats[$i] = 0;
    }
}

// すべて空席の予約をはじくために空席をカウントする 
$empty_seats = 0;
foreach($seats as $row){
    if($row==0){
        $empty_seats++;
    }
}

// メンバーなしの予約をはじくためにメンバーを確認
$members = [];
foreach($_POST['student_numbers'] as $index => $mem){
    // echo "<p>",$mem,":",$index,"</p>";
    $members[$index] = $mem;
}


//「時間帯が重なる」かつ「選んだ席のどれかが予約済み」の予約を探す

// <-- 重なる時間を探す -->
// 開始時間がすでにある予約の終了時間より前
// 終了時間がすでにある予約の開始時間より後
$sql = $pdo->prepare('
select * from reservation
where scheduled_end_time > ?
and 
scheduled_start_time < ?
');

$time_overlap = $sql->execute([$_POST['scheduled_start_time'],$_POST['scheduled_end_time']]);

$results = $sql->fetchAll(PDO::FETCH_ASSOC);
// 中身の確認用
// var_dump($results); 
foreach ($results as $row) {
    // echo "---すでにある予約の開始予定時間と終了予定時間---<br>";
    // echo "開始予定時間 " . $row['scheduled_start_time'] . "<br>";
    // echo "終了予定時間 " . $row['scheduled_end_time'] . "<br>";
    $start = $row['scheduled_start_time'];
    $end = $row['scheduled_end_time'];
}

// echo $start;
// echo $end;

// <-- 選んだ席が予約済みかどうか探す -->

// 時間が重なっている予約の席状況を確認用
foreach ($results as $row) {
    // echo "---すでにある予約の席状況---<br>";
    // echo "seat_1: " . $row['seat_1'] . "<br>";
    // echo "seat_2: " . $row['seat_2'] . "<br>";
    // echo "seat_3: " . $row['seat_3'] . "<br>";
    // echo "seat_4: " . $row['seat_4'] . "<br>";
    // echo "seat_5: " . $row['seat_5'] . "<br>";
}

$seat_overlap = false;
for($i=1;$i<6;$i++){
    // echo '<P>';
    // echo '$seats'.$i.'番目の中身['.$seats[$i].']';
    // echo '$resultsの$seat_['.$i.']の中身['.$row['seat_'.$i].']';
    // echo '</p>';
    if($seats[$i] == 1 && $row['seat_'.$i] == 1){
        $seat_overlap = true;
        break;
    };
}

// var_dump($time_overlap);
// var_dump($seat_overlap);

// <-- 条件を満たせば予約する -->
// セッション情報がなければ不正なアクセスとみなしてはじく
if($no_session){
    echo '<p>';
    echo '不正なアクセスです';
    echo '</p>';
    // onclick='history.back()'はセッション情報をもとに前のページに戻るやつ
    echo "<button>";
    echo "<a href='login-input.php' style='color: inherit; text-decoration: none;'>";
    echo 'ログインする';
    echo "</a>";
    echo '</button>';
//「時間帯が重なる」かつ「選んだ席のどれかが予約済み」なら予約させない
} else if($start != null && $end != null && $seat_overlap){
    echo '<p>';
    echo 'この時間はすでに予約があります';
    echo '</p>';
    // onclick='history.back()'はセッション情報をもとに前のページに戻るやつ
    echo "<button onclick='history.back()'>";
    echo '戻る';
    echo '</button>';
// 全て空席で予約しようとしたらはじく
} else if($empty_seats == 5) {
    echo '<p>';
    echo '席をひとつ以上選択してください';
    echo '</p>';
    echo "<button onclick='history.back()'>";
    echo '戻る';
    echo '</button>';
} else if(empty($members)){
    echo '<p>';
    echo '利用者を一人以上選択してください';
    echo '</p>';
    echo "<button onclick='history.back()'>";
    echo '戻る';
    echo '</button>';
} else{
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
// var_dump($reservation);

// $reservationをそのままでは使えないのでfetchで連想配列（?）にしてそこから取り出す
// そうすると使える
$reservation_id = $reservation->fetch(PDO::FETCH_ASSOC)["reservation_id"];

foreach($_POST['student_numbers'] as $row){
    $sql->execute([$reservation_id,$row]);
}
echo '<p>';
echo '登録しました';
echo '</p>';
echo "<button onclick = location.href='home.php'>";
echo '戻る';
echo '</button>';
} catch(PDOException $e) {
    echo '<p>';    
    echo 'エラー: ' . $e->getMessage();
    echo '</p>';
    echo "<button onclick='history.back()'>";
    echo '戻る';
    echo '</button>';
}
}



?>
