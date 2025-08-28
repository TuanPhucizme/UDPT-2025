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
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn:hover {
            transform: scale(1.02);
            transition: all 0.2s;
        }
    </style>
</head>
<body class="bg-light">

<header class="bg-primary text-white text-center py-4 shadow">
    <h1 class="mb-0">🩺 Hệ Thống Quản Lý Bệnh Viện</h1>
    <p class="mb-0">Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
</header>

<div class="container mt-5">
    <div class="card shadow-lg border-0">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">📋 Danh mục quản lý</h2>
            <div class="d-grid gap-3">
                <a href="./loading/benhnhan.php" class="btn btn-outline-primary btn-lg">👨‍⚕️ Quản lý Bệnh nhân</a>
                <a href="../bacsi/thembacsi.php" class="btn btn-outline-success btn-lg">👩‍⚕️ Quản lý Bác sĩ</a>
                <a href="./loading/lichkham.php" class="btn btn-outline-warning btn-lg">📅 Lịch Khám</a>
                <a href="./loading/donthuoc.php" class="btn btn-outline-info btn-lg">💊 Quản lý Thuốc</a>
                <li><a href="/reports/prescriptions">Báo cáo đơn thuốc</a></li>
                <li><a href="/reports/patients">Báo cáo bệnh nhân</a></li>
                <a href="logout.php" class="btn btn-outline-danger btn-lg">🚪 Đăng xuất</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
