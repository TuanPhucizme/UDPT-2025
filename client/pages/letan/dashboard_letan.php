<?php

// Bước 1: Nạp file configuration
require_once('../../configuration/configuration.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['letan']); // Chỉ lễ tân được vào trang này
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Bảng điều khiển - Lễ tân</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Đường dẫn đến file CSS chung (chứa .card-blur và .action-card) -->
  <link rel="stylesheet" href="../../css/styles.css"> 
  <style>
    .action-card {
        border: 1px solid rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
  </style>
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 1200px;">
        <div class="card-body">

            <!-- 1. Header: Lời chào và thông tin -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h1 class="display-6 fw-bold">
                        <i class="fas fa-concierge-bell text-primary me-2"></i>
                        Chào mừng, Lễ tân
                        <span class="text-primary">Trần Hồng Châu</span>!
                    </h1>
                    <h5 class="text-muted mb-0">Quầy tiếp nhận thông tin bệnh nhân</h5>
                </div>
                <a href="../logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </div>

            <!-- 2. Công cụ chính: Tra cứu và Đặt lịch -->
            <div class="mb-5">
                <h3 class="fw-bold h4 mb-3"><i class="fas fa-tools me-2"></i>Công cụ chính</h3>
                <div class="row g-4">
                    <!-- Thẻ Tra cứu bệnh nhân -->
                    <div class="col-md-6">
                        <a href="tracuu_benhnhan.php" class="text-decoration-none">
                            <div class="card action-card h-100 text-center p-4">
                                <div class="card-body">
                                    <i class="fas fa-search fa-3x text-info mb-3"></i>
                                    <h4 class="card-title">Tra Cứu Hồ Sơ Bệnh Án</h4>
                                    <p class="card-text text-muted">Tìm kiếm thông tin của bệnh nhân đã đăng ký.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <!-- Thẻ Đặt lịch khám mới -->
                    <div class="col-md-6">
                        <a href="datlich_letan.php" class="text-decoration-none">
                            <div class="card action-card h-100 text-center p-4 bg-primary text-white">
                                <div class="card-body">
                                    <i class="fas fa-calendar-plus fa-3x mb-3"></i>
                                    <h4 class="card-title">Đặt Lịch Khám Mới</h4>
                                    <p class="card-text">Tạo một cuộc hẹn mới cho bệnh nhân.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Danh sách lịch hẹn trong ngày -->
            <h3 class="fw-bold h4 mb-3"><i class="fas fa-calendar-day me-2"></i>Lịch hẹn trong ngày hôm nay: <?= date("l, d/m/Y") ?></h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th><i class="fas fa-clock me-1"></i> Giờ hẹn</th>
                            <th><i class="fas fa-user-injured me-1"></i> Bệnh nhân</th>
                            <th><i class="fas fa-user-doctor me-1"></i> Bác sĩ khám</th>
                            <th><i class="fas fa-info-circle me-1"></i> Trạng thái</th>
                            <th><i class="fas fa-cogs me-1"></i> Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu -->
                        <tr>
                            <td>08:30</td>
                            <td class="text-start">Trần Thị B</td>
                            <td class="text-start">BS. Nguyễn Văn An</td>
                            <td><span class="badge bg-secondary">Chưa tới</span></td>
                            <td>
                                <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Xác nhận Check-in">
                                    <i class="fas fa-check-square me-1"></i> Check-in
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>09:00</td>
                            <td class="text-start">Lê Văn Cường</td>
                            <td class="text-start">BS. Lê Thị Dung</td>
                            <td><span class="badge bg-info">Đang chờ khám</span></td>
                            <td>
                                <a href="../bacsi/chitiet_benhan.php" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Xem thông tin">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>09:30</td>
                            <td class="text-start">Phạm Minh Anh</td>
                            <td class="text-start">BS. Trần Hùng</td>
                            <td><span class="badge bg-primary">Đang trong phòng khám</span></td>
                            <td>
                                <a href="../admin/dashboard_admin.php" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Xem thông tin">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                         <tr>
                            <td>10:00</td>
                            <td class="text-start">Vũ Ngọc Mai</td>
                            <td class="text-start">BS. Nguyễn Văn An</td>
                            <td><span class="badge bg-success">Đã khám xong</span></td>
                             <td>
                                <a href="#" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Xem thông tin">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Script để kích hoạt Tooltip của Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
</script>

</body>
</html>