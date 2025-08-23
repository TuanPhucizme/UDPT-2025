<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bệnh Viện ABC - Hệ Thống Quản Lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .nav-item {
            margin: 0 0.5rem;
        }
        .user-info {
            color: rgba(255,255,255,0.8);
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">🏥 Bệnh Viện ABC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['user'])): ?>
                        <a class="nav-link" href="/home">Trang Chủ</a>
                        <a class="nav-link" href="/patients">Bệnh Nhân</a>
                        <?php if (in_array($_SESSION['user']['role'], ['bacsi', 'admin'])): ?>
                            <a class="nav-link" href="/appointments">Lịch Khám</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'benhnhan'): ?>
                    <form class="d-flex me-3" action="/patients/search" method="GET">
                        <input class="form-control me-2" type="search" 
                               placeholder="Tìm bệnh nhân..." 
                               name="name"
                               aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                <?php endif; ?>

                <div class="d-flex align-items-center">
                    <?php if (isset($_SESSION['user'])): ?>
                        <span class="user-info">
                            <i class="fas fa-user"></i> 
                            <?= htmlspecialchars($_SESSION['user']['name']) ?>
                        </span>
                        <a href="/auth/logout" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                        </a>
                    <?php else: ?>
                        <a href="/auth/login" class="btn btn-outline-light">
                            <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <main class="py-4 mt-5">