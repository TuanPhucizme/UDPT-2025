<?php

// Bước 1: Nạp file config
require_once('../../configuration/config.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['bacsi']); // Chỉ bác sĩ được vào trang này
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Bảng điều khiển - Bác sĩ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Đường dẫn đến file CSS chung của bạn -->
  <link rel="stylesheet" href="../../css/styles.css"> 
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 py-5">
    <!-- Sử dụng card-blur để tạo giao diện đồng nhất -->
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 1200px;">
        <div class="card-body">

            <!-- 1. Lời chào và Nút Đăng xuất -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h1 class="display-6 fw-bold">
                        <i class="fas fa-stethoscope text-primary me-2"></i>
                        Chào mừng, Bác sĩ 
                        <!-- PHP/Backend sẽ điền tên bác sĩ vào đây -->
                        <span class="text-primary">Nguyễn Văn An</span>!
                    </h1>
                    <h5 class="text-muted mb-0">Hôm nay: <?= date("l, d/m/Y") ?></h5>
                    <h5 class="text-muted mb-0 mt-2">Đây là lịch khám và công việc của bạn hôm nay.</h5>
                </div>
                <a href="../logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </div>

            <!-- 2. Thanh tra cứu bệnh nhân -->
            <div class="mb-4">
                <h3 class="fw-bold h4">
                    <i class="fas fa-search me-2"></i>Tra cứu nhanh
                </h3>
                <form id="searchForm" method="GET" action="tim_benh_nhan.php">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" name="keyword" placeholder="Nhập tên bệnh nhân hoặc Mã hồ sơ bệnh án (HSBA)..." required>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <hr class="my-4">

            <!-- 3. Danh sách lịch khám -->
            <h3 class="fw-bold h4 mb-3">
                <i class="fas fa-calendar-check me-2"></i>Danh sách lịch khám hôm nay
            </h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th><i class="fas fa-clock me-1"></i> Giờ khám</th>
                            <th><i class="fas fa-user-injured me-1"></i> Tên Bệnh nhân</th>
                            <th><i class="fas fa-id-card me-1"></i> Mã HSBA</th>
                            <th><i class="fas fa-notes-medical me-1"></i> Lý do khám</th>
                            <th><i class="fas fa-info-circle me-1"></i> Trạng thái</th>
                            <th><i class="fas fa-cogs me-1"></i> Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu 1 -->
                        <tr>
                            <td>08:30</td>
                            <td class="text-start">Trần Thị B</td>
                            <td>BN-00123</td>
                            <td class="text-start">Khám định kỳ, đau đầu</td>
                            <td><span class="badge bg-warning text-dark">Đang chờ</span></td>
                            <td>
                                <a href="chitiet_benhan.php" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Xem hồ sơ chi tiết">
                                    <i class="fas fa-file-lines"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        <!-- Dữ liệu mẫu 2 -->
                        <tr>
                            <td>09:00</td>
                            <td class="text-start">Lê Văn Cường</td>
                            <td>BN-00456</td>
                            <td class="text-start">Tái khám sau phẫu thuật</td>
                            <td><span class="badge bg-warning text-dark">Đang chờ</span></td>
                            <td>
                                <a href="chitiet_benhan.php" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Xem hồ sơ chi tiết">
                                    <i class="fas fa-file-lines"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        <!-- Dữ liệu mẫu 3 (đã khám) -->
                         <tr>
                            <td>09:30</td>
                            <td class="text-start">Phạm Minh Anh</td>
                            <td>BN-00789</td>
                            <td class="text-start">Ho, sốt nhẹ</td>
                            <td><span class="badge bg-success">Đã khám</span></td>
                            <td>
                                <a href="chitiet_benhan.php?id=BN-00789" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Xem lại hồ sơ">
                                    <i class="fas fa-history"></i> Xem lại
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