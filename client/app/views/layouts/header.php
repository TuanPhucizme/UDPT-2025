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
                        <?php if (in_array($_SESSION['user']['role'], ['bacsi', 'admin','letan'])): ?>
                            <a class="nav-link" href="/appointments">Lịch Khám</a>
                        <?php endif; ?>
                        <?php if ($_SESSION['user']['role'] === 'duocsi'): ?>
                            <a class="nav-link" href="/prescriptions/pending">
                                <span>Phát thuốc</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="/medicines" class="nav-link"> Quản lý Thuốc
                            </a>
                        <?php endif; ?>
                </div>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                <ul class="navbar-nav ms-auto">
                    <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                        <?php
                        // Get low stock count - in a real app, this would come from a controller
                        require_once '../app/services/PrescriptionService.php';
                        $prescriptionService = new PrescriptionService();
                        $lowStockResult = $prescriptionService->getAllMedicines(['stock_status' => 'low']);
                        $lowStockCount = count($lowStockResult??[]);
                        ?>
                        
                        <?php if ($lowStockCount > 0): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter"><?= $lowStockCount ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Cảnh báo kho thuốc
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="/medicines?filter=low">
                                    <div class="me-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500"><?= date('d/m/Y') ?></div>
                                        <span class="font-weight-bold"><?= $lowStockCount ?> loại thuốc sắp hết hàng</span>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="/medicines?filter=low">Xem tất cả</a>
                            </div>
                        </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Rest of the navbar items -->
                </ul>
                <?php endif; ?>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'benhnhan'): ?>
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
        <div class="container">
            <div class="row">

                <div class="col-md-9">
                    <!-- Main Content -->
                    <!-- Content will be injected here based on the page -->
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Custom JavaScript can be added here
    </script>
</body>
</html>