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

function remove_space($str)
{
    $str = str_replace(" ", "", $str);
    $str = str_replace("　", "", $str);
    return $str;
}

$name = remove_space($_POST['name']);
$student_number = remove_space($_POST['student_number']);
$password = remove_space($_POST['password']);



$sql = $pdo->prepare('select * from user 
                        WHERE REPLACE(REPLACE(user_name, " ", ""), "　", "") = ? 
                        AND student_number = ? 
                        AND password = ?');
$sql->execute([$name, $student_number, $password]);
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
    echo '<h1>ログイン名またはパスワードが違います。</h1>';
    echo '<a href="login-input.php">戻る</a>';
}

?>