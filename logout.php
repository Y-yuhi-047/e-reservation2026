<?php
// セッションを開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// セッション変数をすべて解除
$_SESSION = array();

// セッションを破壊
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// ログアウト後、ログイン画面にリダイレクト
header('Location: login-input.php'); 
exit();
?>