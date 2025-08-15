<?php

// Bước 1: Nạp file configuration
require_once('../../configuration/configuration.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['duocsi', 'bacsi', 'admin']); // Chỉ dược sĩ, bác sĩ và admin được vào trang này
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết Đơn thuốc</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Đường dẫn đến file CSS chung của bạn -->
  <link rel="stylesheet" href="../../css/styles.css"> 
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 900px;">
        <div class="card-body">

            <!-- 1. Header của trang -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h1 class="display-6 fw-bold">
                        <i class="fas fa-file-prescription text-primary me-2"></i>
                        Chi tiết Đơn thuốc
                    </h1>
                    <!-- Dữ liệu PHP: Mã Đơn Thuốc -->
                    <p class="h4 text-muted mb-0">Mã đơn: <span class="text-primary">DT12345</span></p>
                </div>
                <a href="dashboard_duocsi.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay về Dashboard
                </a>
            </div>

            <!-- 2. Thông tin chung của đơn thuốc -->
            <div class="bg-light p-3 rounded mb-4">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong><i class="fas fa-user-injured me-2"></i>Bệnh nhân:</strong>
                        <!-- Dữ liệu PHP -->
                        <span>Trần Thị B</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong><i class="fas fa-user-doctor me-2"></i>Bác sĩ kê đơn:</strong>
                        <!-- Dữ liệu PHP -->
                        <span>BS. Nguyễn Văn An</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong><i class="fas fa-calendar-alt me-2"></i>Ngày kê đơn:</strong>
                        <!-- Dữ liệu PHP -->
                        <span>03/08/2025</span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong><i class="fas fa-info-circle me-2"></i>Trạng thái:</strong>
                        <!-- Dữ liệu PHP -->
                        <span class="badge bg-danger">Chờ xử lý</span>
                    </div>
                </div>
            </div>

            <!-- 3. PHẦN MỚI: Tóm tắt để chuẩn bị thuốc -->
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-box-check me-2"></i>Tóm tắt chuẩn bị thuốc</h4>
                </div>
                <ul class="list-group list-group-flush">
                    <!-- Dữ liệu mẫu (sẽ được lặp bằng PHP) -->
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Paracetamol 500mg
                        <span class="badge bg-primary rounded-pill fs-6">Tổng số lượng: 6 viên</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Amoxicillin 250mg
                        <span class="badge bg-primary rounded-pill fs-6">Tổng số lượng: 14 viên</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Clorpheniramin 4mg
                        <span class="badge bg-primary rounded-pill fs-6">Tổng số lượng: 3 viên</span>
                    </li>
                </ul>
            </div>

 <!-- 4. Bảng chi tiết các loại thuốc (đã nâng cấp) -->
            <h3 class="fw-bold h4 mb-3"><i class="fas fa-pills me-2"></i>Chi tiết liều lượng</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Tên thuốc</th>
                            <th>Liều / Lần</th>
                            <th>Thời gian uống</th>
                            <th>Số ngày</th>
                            <th>Lưu ý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu (sẽ được lặp bằng PHP) -->
                        <tr>
                            <td><strong>Paracetamol 500mg</strong></td>
                            <td class="text-center">1 viên</td>
                            <td>Buổi sáng, Buổi tối</td>
                            <td class="text-center">2 ngày</td>
                            <td class="text-center">Uống sau ăn, không quá 4 viên/ngày</td>
                        </tr>
                        <tr>
                            <td><strong>Amoxicillin 250mg</strong></td>
                            <td class="text-center">1 viên</td>
                            <td>Buổi sáng.</td>
                            <td class="text-center">7 ngày</td>
                            <td class="text-center">Uống đủ liệu trình, không bỏ liều</td>
                        </tr>
                        <tr>
                            <td><strong>Clorpheniramin 4mg</strong></td>
                            <td class="text-center">1 viên</td>
                            <td>Uống buổi tối</td>
                            <td class="text-center">3 ngày</td>
                            <td class="text-center">Uống trước khi ngủ 30 phút.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr class="my-4">

            <!-- 4. Nút hành động xác nhận -->
            <form action="xacnhan_giao_thuoc.php" method="POST" class="text-center">
                <!-- Truyền ID đơn thuốc để xử lý ở backend -->
                <input type="hidden" name="donthuoc_id" value="DT12345">
                
                <p class="text-muted">Kiểm tra kỹ thông tin trước khi xác nhận cấp phát thuốc cho bệnh nhân.</p>
                
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle me-2"></i>Xác nhận Đã Giao Thuốc
                </button>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>