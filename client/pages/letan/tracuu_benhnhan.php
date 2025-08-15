<?php

// Bước 1: Nạp file configuration
require_once('../../configuration/configuration.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['admin', 'letan', 'bacsi']); // Chỉ admin, lễ tân và bác sĩ được vào trang này

// --- LOGIC TÌM KIẾM ---
$keyword = $_GET['keyword'] ?? ''; // Lấy từ khóa từ URL
$results = []; // Mảng để chứa kết quả

// Chỉ thực hiện truy vấn nếu có từ khóa được gửi lên
if (!empty($keyword)) {
    // TODO (Backend): Viết câu lệnh SQL an toàn để tìm kiếm bệnh nhân
    // Ví dụ: "SELECT id, hoten, ngaysinh, sdt FROM benhnhan WHERE hoten LIKE ? OR sdt LIKE ? OR id LIKE ?"
    // Sử dụng prepared statements để chống SQL Injection
    
    // Dữ liệu giả để minh họa
    $results = [
        ['id' => 'BN-00123', 'hoten' => 'Trần Thị B', 'ngaysinh' => '1985-08-15', 'sdt' => '0987654321'],
        ['id' => 'BN-00456', 'hoten' => 'Trần Văn An', 'ngaysinh' => '1992-03-20', 'sdt' => '0912345678'],
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu Hồ sơ Bệnh nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/styles.css"> 
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 p-3 p-md-5">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 1000px;">
        <div class="card-body">

            <!-- 1. Header và Form tìm kiếm -->
            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold"><i class="fas fa-search me-2"></i>Tra cứu Hồ sơ Bệnh nhân</h1>
                <p class="text-muted">Nhập Tên, Số điện thoại hoặc Mã bệnh nhân để tìm kiếm.</p>
            </div>

            <form method="GET" action="" class="mb-5">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="keyword" placeholder="Nhập từ khóa..." value="<?= htmlspecialchars($keyword) ?>" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Tìm kiếm
                    </button>
                </div>
            </form>

            <!-- 2. Bảng kết quả (Chỉ hiển thị khi có hành động tìm kiếm) -->
            <?php if (!empty($keyword)): ?>
                <hr>
                <h3 class="fw-bold text-center mb-4">Kết quả tìm kiếm cho "<?= htmlspecialchars($keyword) ?>"</h3>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>Mã BN</th>
                                <th>Họ tên</th>
                                <th>Ngày sinh</th>
                                <th>Số điện thoại</th>
                                <th style="width: 15%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($results)): ?>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['hoten']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($row['ngaysinh']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($row['sdt']) ?></td>
                                        <td class="text-center">
                                            <a href="../bacsi/chitiet_benhan.php" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye me-1"></i>Xem Hồ sơ
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">
                                        <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                        Không tìm thấy bệnh nhân nào khớp với từ khóa của bạn.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($_GET['keyword'])): // Trường hợp tìm kiếm nhưng không nhập gì ?>
                 <div class="alert alert-warning text-center">Vui lòng nhập từ khóa để bắt đầu tìm kiếm.</div>
            <?php else: // Giao diện mặc định ban đầu ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    Bảng kết quả sẽ được hiển thị ở đây sau khi bạn thực hiện tìm kiếm.
                </div>
            <?php endif; ?>

            <!-- Nút quay về -->
            <div class="text-center mt-4">
                <a href="dashboard_letan.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay về Bàn làm việc
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>