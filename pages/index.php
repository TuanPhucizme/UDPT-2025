<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bệnh viện</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h1>Hệ Thống Quản Lý Bệnh Viện</h1>
    <p>Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
</header>

<div class="container">
    <h2>Danh mục quản lý</h2>
    <div class="menu">
        <a href="benhnhan.php">Quản lý Bệnh nhân</a>
        <a href="bacsi.php">Quản lý Bác sĩ</a>
        <a href="lichhen.php">Lịch hẹn</a>
        <a href="thuoc.php">Quản lý Thuốc</a>
        <a href="logout.php">Đăng xuất</a>
    </div>
</div>

</body>
</html>
