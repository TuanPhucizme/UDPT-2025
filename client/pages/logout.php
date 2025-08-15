<?php
// File: /pages/logout.php

// Nạp file configuration để có BASE_URL
require_once('../configuration/configuration.php');

// Bắt đầu session để có thể hủy nó
session_start();

// Hủy tất cả các biến session
$_SESSION = array();

// Nếu có cookie session, cũng hủy nó đi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cuối cùng, hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập một cách an toàn
header("Location: " . BASE_URL . "/pages/login.php");
exit();
?>