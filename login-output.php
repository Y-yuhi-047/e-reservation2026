<?php
session_start();
?>
<?php

unset($_SESSION['user']);

$pdo = new PDO(
    'mysql:host=localhost;dbname=e-reservation;charset=utf8',
    'root',
    'root'
);

$sql = $pdo->prepare('select * from user where user_name=? and student_number=? and password=?');
$sql->execute([$_POST['name'], $_POST['student_number'], $_POST['password']]);
foreach ($sql as $row) {
    $_SESSION['user'] = [
        'number' => $row['student_number'],
        'profile_picture' => $row['profile_picture'],
        'name' => $row['user_name'],
        'password' => $row['password']
    ];
}
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit();
} else {
    echo 'ログイン名またはパスワードが違います。';
    echo '<a href="login-input.php">戻る</a>';
}

?>