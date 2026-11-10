<?php require 'header.php'?>
<?php session_start() ?>
<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=e-reservation;charset=utf8mb4',
    'root',
    'root',
);
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
echo '<pre>';
print_r($_POST);
echo '</pre>';

// reservationテーブルに入れる内容と順番
// 予約id,開始予定日時、終了予定日時,開始日時、終了日時,ゲームid,席1,2,3,4,5,
// 予約作成者、予約編集者、予約作成日時、予約編集日時、メモ
$sql = $pdo->prepare('insert into reservation values(
null,?,?,null,null,?,?,?,?,?,?,?,?,?,?,?)');

$sql->execute();
?>