<?php

// Bước 1: Nạp file configuration
require_once('../../configuration/configuration.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['duocsi']); // Chỉ dược sĩ được vào trang này
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Bảng điều khiển - Dược sĩ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Đường dẫn đến file CSS chung của bạn -->
  <link rel="stylesheet" href="../../css/styles.css"> 
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
                        Chào mừng, Dược sĩ
                        <span class="text-primary">Lê Văn Bình</span>!
                    </h1>
                    <h5 class="text-muted mb-0">Hôm nay: <?= date("l, d/m/Y") ?></h5>
                </div>
                <a href="../logout.php" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </div>

            <!-- 2. Thanh tra cứu đơn thuốc -->
            <div class="mb-4">
                <h3 class="fw-bold h4"><i class="fas fa-search me-2"></i>Tra cứu đơn thuốc</h3>
                <form id="searchForm" method="GET" action="tim_don_thuoc.php">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" name="keyword" placeholder="Nhập tên bệnh nhân, bác sĩ hoặc mã đơn thuốc..." required>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <hr class="my-4">

            <!-- 3. Danh sách đơn thuốc mới -->
            <h3 class="fw-bold h4 mb-3"><i class="fas fa-list-ul me-2"></i>Đơn thuốc mới cần xử lý</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th><i class="fas fa-clock me-1"></i> Thời gian kê đơn</th>
                            <th><i class="fas fa-user-injured me-1"></i> Bệnh nhân</th>
                            <th><i class="fas fa-user-doctor me-1"></i> Bác sĩ kê đơn</th>
                            <th><i class="fas fa-info-circle me-1"></i> Trạng thái</th>
                            <th><i class="fas fa-cogs me-1"></i> Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu -->
                        <tr>
                            <td>08:45 - 03/08/2025</td>
                            <td class="text-start">Trần Thị B</td>
                            <td class="text-start">BS. Nguyễn Văn An</td>
                            <td><span class="badge bg-danger">Mới, chờ cấp phát</span></td>
                            <td>
                                <a href="chitiet_donthuoc.php" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Xem chi tiết và xử lý đơn thuốc">
                                    <i class="fas fa-file-lines"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>09:15 - 03/08/2025</td>
                            <td class="text-start">Lê Văn Cường</td>
                            <td class="text-start">BS. Lê Thị Dung</td>
                            <td><span class="badge bg-danger">Mới, chờ cấp phát</span></td>
                            <td>
                                <a href="chitiet_donthuoc.php?id=DT12346" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Xem chi tiết và xử lý đơn thuốc">
                                    <i class="fas fa-file-lines"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>09:40 - 03/08/2025</td>
                            <td class="text-start">Phạm Minh Anh</td>
                            <td class="text-start">BS. Trần Hùng</td>
                            <td><span class="badge bg-info">Đã soạn, chờ giao</span></td>
                            <td>
                                <a href="chitiet_donthuoc.php?id=DT12347" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Xem lại thông tin">
                                    <i class="fas fa-eye"></i> Xem lại
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>10:10 - 03/08/2025</td>
                            <td class="text-start">Vũ Ngọc Mai</td>
                            <td class="text-start">BS. Nguyễn Văn An</td>
                            <td><span class="badge bg-success">Đã hoàn thành</span></td>
                             <td>
                                <a href="chitiet_donthuoc.php?id=DT12348" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Xem lại thông tin">
                                    <i class="fas fa-eye"></i> Xem lại
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