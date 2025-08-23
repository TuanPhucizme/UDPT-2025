<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bệnh Viện ABC - Hệ Thống Quản Lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/layouts/header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <h1 class="display-4 mb-4">Chào mừng đến với Hệ thống Quản lý Bệnh viện ABC</h1>
                <p class="lead mb-5">Hệ thống quản lý hiện đại giúp tối ưu hóa quy trình khám chữa bệnh</p>
                
                <?php if (!isset($_SESSION['user'])): ?>
                    <a href="/auth/login" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập Ngay
                    </a>
                <?php else: ?>
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-injured fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Quản lý Bệnh nhân</h5>
                                    <p class="card-text">Tra cứu và quản lý thông tin bệnh nhân</p>
                                    <a href="/patients" class="btn btn-outline-primary">Truy cập</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Lịch Khám</h5>
                                    <p class="card-text">Đặt lịch và quản lý cuộc hẹn</p>
                                    <a href="/appointments" class="btn btn-outline-success">Truy cập</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-prescription fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Đơn Thuốc</h5>
                                    <p class="card-text">Quản lý và tra cứu đơn thuốc</p>
                                    <a href="/prescriptions" class="btn btn-outline-info">Truy cập</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 justify-content-center">
                        <?php if (isset($_SESSION['user'])): ?>
                            <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-calendar-plus fa-3x text-primary mb-3"></i>
                                            <h5 class="card-title">Đặt Lịch Khám</h5>
                                            <p class="card-text">Tạo lịch hẹn mới cho bệnh nhân</p>
                                            <a href="/appointments/create" class="btn btn-outline-primary">Đặt Lịch</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (in_array($_SESSION['user']['role'], ['bacsi', 'admin'])): ?>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                                            <h5 class="card-title">Lịch Hẹn Chờ Duyệt</h5>
                                            <p class="card-text">Xem và duyệt các yêu cầu lịch hẹn</p>
                                            <a href="/appointments/pending" class="btn btn-outline-success">Xem Lịch</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>