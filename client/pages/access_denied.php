<?php
// Bắt đầu session để có thể đọc được vai trò của người dùng
session_start();

// Mặc định, nếu không xác định được vai trò, sẽ chuyển về trang login
$dashboard_url = 'login.php'; 

// Xác định đúng trang dashboard dựa trên vai trò đã lưu trong session
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            $dashboard_url = 'admin/dashboard_admin.php';
            break;
        case 'bacsi':
            $dashboard_url = 'bacsi/dashboard_bacsi.php';
            break;
        case 'duocsi':
            $dashboard_url = 'duocsi/dashboard_duocsi.php';
            break;
        case 'letan':
            $dashboard_url = 'letan/dashboard_letan.php';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Truy cập bị từ chối</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Đảm bảo đường dẫn này chính xác so với vị trí file access_denied.php -->
  <link rel="stylesheet" href="../../css/styles.css"> 
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 p-3">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 600px;">
        <div class="card-body text-center">

            <i class="fas fa-ban fa-5x text-danger mb-4"></i>

            <h1 class="display-5 fw-bold">Truy cập bị từ chối</h1>

            <p class="lead text-muted">
                Rất tiếc, tài khoản của bạn không có quyền truy cập vào trang này.
            </p>

            <div class="d-flex justify-content-center gap-2 mt-4">
                <!-- Nút này sẽ tự động trỏ đến đúng trang dashboard của người dùng -->
                <a href="<?= htmlspecialchars($dashboard_url) ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Về trang của tôi
                </a>
                <a href="logout.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>