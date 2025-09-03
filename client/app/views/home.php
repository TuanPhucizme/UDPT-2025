<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bệnh Viện ABC - Hệ Thống Quản Lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd, #f8f9fa);
            min-height: 100vh;
        }
        .hero-section {
            padding: 45px 20px;
            border-radius: 20px;
            background: linear-gradient(135deg, #42a5f5, #1e88e5);
            color: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            margin-bottom: 0.5rem;
        }
        .dashboard-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .dashboard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .dashboard-card i {
            transition: transform 0.25s ease;
        }
        .dashboard-card:hover i {
            transform: scale(1.2);
        }
        .section-title {
            font-weight: bold;
            margin: 40px 0 20px;
            color: #333;
            text-transform: uppercase;
            border-left: 5px solid #0d6efd;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/layouts/header.php'; ?>

    <div class="container py-1">
        <div class="row">
            <div class="col-12 text-center">
                <div class="hero-section">
                    <h1 class="display-5 fw-bold mb-3">Hệ thống Quản lý Bệnh viện ABC</h1>
                    <p class="lead mb-5">Giải pháp quản lý hiện đại, tối ưu hóa quy trình khám chữa bệnh</p>

                    <?php if (!isset($_SESSION['user'])): ?>
                        <a href="/auth/login" class="btn btn-light btn-lg px-5 shadow">
                            <i class="fas fa-sign-in-alt me-2"></i> Đăng Nhập Ngay
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] !== 'duocsi'): ?>
            <!-- Dashboard chính -->
            <h4 class="section-title">Chức năng chính</h4>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="card dashboard-card h-100 text-center p-3">
                        <i class="fas fa-user-injured fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">Quản lý Bệnh nhân</h5>
                        <p class="text-muted">Tra cứu và quản lý thông tin bệnh nhân</p>
                        <a href="/patients" class="btn btn-outline-primary">Truy cập</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100 text-center p-3">
                        <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                        <h5 class="fw-bold">Lịch Khám</h5>
                        <p class="text-muted">Đặt lịch và quản lý cuộc hẹn</p>
                        <a href="/appointments" class="btn btn-outline-success">Truy cập</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card h-100 text-center p-3">
                        <i class="fas fa-prescription fa-3x text-info mb-3"></i>
                        <h5 class="fw-bold">Đơn Thuốc</h5>
                        <p class="text-muted">Quản lý và tra cứu đơn thuốc</p>
                        <a href="/prescriptions" class="btn btn-outline-info">Truy cập</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>


            <!-- Lễ tân / Admin -->
            <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                <h4 class="section-title">Lễ tân</h4>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card dashboard-card h-100 text-center p-3">
                            <i class="fas fa-calendar-plus fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold">Đặt Lịch Khám</h5>
                            <p class="text-muted">Tạo lịch hẹn mới cho bệnh nhân</p>
                            <a href="/appointments/create" class="btn btn-outline-primary">Đặt Lịch</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dược sĩ -->
            <?php if ($_SESSION['user']['role'] === 'duocsi'): ?>
                <h4 class="section-title">Quản lý thuốc</h4>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-pills fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold">Danh sách thuốc</h5>
                            <a href="/medicines" class="btn btn-primary">Xem</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-prescription-bottle-alt fa-3x text-success mb-3"></i>
                            <h5 class="fw-bold">Nhập thuốc</h5>
                            <a href="/medicines/create" class="btn btn-success">Nhập</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-chart-pie fa-3x text-info mb-3"></i>
                            <h5 class="fw-bold">Báo cáo thuốc</h5>
                            <a href="/medicines/report" class="btn btn-info">Xem</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-clipboard-check fa-3x text-warning mb-3"></i>
                            <h5 class="fw-bold">Đơn thuốc chờ</h5>
                            <a href="/prescriptions/pending" class="btn btn-warning">Phát</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Admin -->
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <h4 class="section-title">Báo cáo & Phân tích</h4>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                            <h5 class="fw-bold">Báo cáo tổng quan</h5>
                            <a href="/reports" class="btn btn-primary">Xem</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-users fa-3x text-info mb-3"></i>
                            <h5 class="fw-bold">Báo cáo bệnh nhân</h5>
                            <a href="/reports/patients" class="btn btn-info">Xem</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-hospital fa-3x text-success mb-3"></i>
                            <h5 class="fw-bold">Báo cáo chuyên khoa</h5>
                            <a href="/reports/departments" class="btn btn-success">Xem</a>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-center p-3">
                            <i class="fas fa-pills fa-3x text-warning mb-3"></i>
                            <h5 class="fw-bold">Báo cáo thuốc</h5>
                            <a href="/reports/medicines" class="btn btn-warning">Xem</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
