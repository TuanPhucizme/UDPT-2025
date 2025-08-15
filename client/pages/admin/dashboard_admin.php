<?php

// Bước 1: Nạp file configuration
require_once('../../configuration/configuration.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['admin']); // Chỉ admin được vào trang này
?>


<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Bảng điều khiển - Quản trị viên</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/styles.css"> <!-- File CSS chung -->
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 p-3 p-md-5">
    <div class="card shadow-lg p-4 w-100 card-blur">
        <div class="card-body">

            <!-- 1. Header -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="display-6 fw-bold"><i class="fas fa-shield-halved text-primary me-2"></i>Bảng điều khiển Quản trị viên</h1>
                <a href="../logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a>
            </div>

            <!-- 2. Thẻ Thống kê Nhanh -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6"><div class="card bg-primary text-white shadow"><div class="card-body d-flex align-items-center"><i class="fas fa-users fa-3x me-3"></i><div><div class="fs-4 fw-bold">1,250</div><div class="small">Bệnh nhân / Tháng</div></div></div></div></div>
                <div class="col-lg-3 col-md-6"><div class="card bg-info text-white shadow"><div class="card-body d-flex align-items-center"><i class="fas fa-pills fa-3x me-3"></i><div><div class="fs-4 fw-bold">5,678</div><div class="small">Đơn thuốc đã cấp</div></div></div></div></div>
                <div class="col-lg-3 col-md-6"><div class="card bg-success text-white shadow"><div class="card-body d-flex align-items-center"><i class="fas fa-user-doctor fa-3x me-3"></i><div><div class="fs-4 fw-bold">85</div><div class="small">Tổng số Nhân viên</div></div></div></div></div>
                <div class="col-lg-3 col-md-6"><div class="card bg-warning text-dark shadow"><div class="card-body d-flex align-items-center"><i class="fas fa-warehouse fa-3x me-3"></i><div><div class="fs-4 fw-bold">25</div><div class="small">Thuốc sắp hết</div></div></div></div></div>
            </div>

            <!-- 3. Biểu đồ và Công cụ Quản lý -->
            <div class="row g-4">
                <!-- Cột trái: Biểu đồ chính -->
                <div class="col-lg-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2"></i>Thống kê Bệnh nhân (12 tháng qua)</h5></div>
                        <div class="card-body">
                            <canvas id="patientChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Thông tin phụ và công cụ -->
                <div class="col-lg-4">
                    <!-- Cơ cấu nhân sự -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-sitemap me-2"></i>Cơ cấu Nhân sự</h5></div>
                        <div class="card-body">
                             <canvas id="staffChart" style="max-height: 200px;"></canvas>
                        </div>
                    </div>
                    
                    <!-- Hộp công cụ quản trị -->
                    <div class="card shadow-sm">
                        <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-tools me-2"></i>Hộp công cụ Quản trị</h5></div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="quanly_nhanvien.php" class="btn btn-outline-dark"><i class="fas fa-users-cog me-2"></i>Quản lý Nhân viên</a>
                                <a href="quanly_thuoc.php" class="btn btn-outline-dark"><i class="fas fa-capsules me-2"></i>Quản lý Thuốc & Kho</a>
                                <a href="quanly_taikhoan.php" class="btn btn-outline-dark"><i class="fas fa-user-shield me-2"></i>Quản lý Tài khoản Hệ thống</a>
                                <a href="xem_thongke.php" class="btn btn-outline-dark"><i class="fas fa-chart-pie me-2"></i>Xem Thống kê Chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts: Bootstrap và Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dữ liệu mẫu - Backend sẽ cung cấp dữ liệu thật
    const patientData = {
        labels: ['Thg 9', 'Thg 10', 'Thg 11', 'Thg 12', 'Thg 1', 'Thg 2', 'Thg 3', 'Thg 4', 'Thg 5', 'Thg 6', 'Thg 7', 'Thg 8'],
        datasets: [{
            label: 'Số lượng bệnh nhân',
            data: [650, 590, 800, 810, 560, 550, 400, 900, 1100, 1050, 1200, 1250],
            fill: true,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    };

    const staffData = {
        labels: ['Bác sĩ', 'Dược sĩ', 'Lễ tân'],
        datasets: [{
            label: 'Cơ cấu nhân sự',
            data: [45, 15, 25], // Tổng 85
            backgroundColor: ['rgba(54, 162, 235, 0.8)', 'rgba(75, 192, 192, 0.8)', 'rgba(255, 206, 86, 0.8)'],
            hoverOffset: 4
        }]
    };

    // Vẽ Biểu đồ Bệnh nhân
    const patientCtx = document.getElementById('patientChart').getContext('2d');
    new Chart(patientCtx, {
        type: 'line',
        data: patientData,
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Vẽ Biểu đồ Nhân sự
    const staffCtx = document.getElementById('staffChart').getContext('2d');
    new Chart(staffCtx, {
        type: 'doughnut',
        data: staffData,
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>

</body>
</html>