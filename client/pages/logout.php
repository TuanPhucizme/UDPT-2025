<?php
session_start();
session_destroy(); // Xoá toàn bộ session
header("Location: login.php"); // Quay lại trang đăng nhập
exit();
?>