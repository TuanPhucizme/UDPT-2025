<?php

// Bước 1: Nạp file config
require_once('../../configuration/config.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['letan']); // Chỉ lễ tân được vào trang này
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký và Yêu cầu Khám bệnh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Đảm bảo đường dẫn này chính xác -->
    <link rel="stylesheet" href="../../css/styles.css"> 
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 py-5">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 900px;">
        <div class="card-body">

            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold">
                    <i class="fas fa-user-plus me-2"></i>Đăng ký Khám bệnh
                </h1>
                <p class="text-muted">Điền thông tin bệnh nhân và yêu cầu khám để bắt đầu.</p>
            </div>

            <form action="/xuly_dangky_va_datlich.php" method="POST" id="formDangKyBenhNhan">
                
                <!-- 1. Phần Thông tin Hành chính -->
                <h4 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-id-card me-2"></i>Thông tin Cá nhân
                </h4>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Họ và Tên</label><div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span><input type="text" class="form-control" name="ten" placeholder="Ví dụ: Nguyễn Văn An" required></div></div>
                    <div class="col-md-6"><label class="form-label">Ngày Sinh</label><div class="input-group"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span><input type="date" class="form-control" name="ngaysinh" required></div></div>
                    <div class="col-md-6"><label class="form-label">Giới tính</label><div class="input-group"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span><select class="form-select" name="gioitinh" required><option value="" disabled selected>-- Chọn giới tính --</option><option value="Nam">Nam</option><option value="Nữ">Nữ</option><option value="Khác">Khác</option></select></div></div>
                    <div class="col-md-6"><label class="form-label">Số điện thoại</label><div class="input-group"><span class="input-group-text"><i class="fas fa-phone"></i></span><input type="tel" class="form-control" name="sdt" placeholder="Số điện thoại liên lạc" required></div></div>
                    <div class="col-md-6"><label class="form-label">Email</label><div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span><input type="email" class="form-control" name="email" placeholder="Email liên lạc" required></div></div>
                    <div class="col-12"><label class="form-label">Địa chỉ</label><div class="input-group"><span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span><input type="text" class="form-control" name="diachi" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required></div></div>
                </div>

                <hr class="my-4">

                <!-- 2. Phần Tiền sử bệnh án -->
                <h4 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-notes-medical me-2"></i>Tiền sử Bệnh án
                </h4>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label">Dị ứng</label><textarea class="form-control" name="diung" rows="2" placeholder="Ghi rõ các loại dị ứng nếu có. Nếu không, ghi 'Không có'."></textarea></div>
                    <div class="col-12"><label class="form-label">Bệnh mãn tính</label><textarea class="form-control" name="benh_mantinh" rows="2" placeholder="Liệt kê các bệnh mãn tính đang điều trị. Nếu không, ghi 'Không có'."></textarea></div>
                </div>

                <hr class="my-4">

                <!-- 3. PHẦN MỚI: Yêu cầu Khám Bệnh -->
                <h4 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-stethoscope me-2"></i>Yêu cầu Khám Bệnh
                </h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="chuyen_khoa" class="form-label">Khám chuyên khoa</label>
                        <div class="input-group">
                             <span class="input-group-text"><i class="fas fa-clinic-medical"></i></span>
                            <select class="form-select" name="chuyen_khoa" required>
                                <option value="" disabled selected>-- Chọn chuyên khoa --</option>
                                <option value="kham_tong_quat">Khám tổng quát</option>
                                <option value="noi_khoa">Nội khoa</option>
                                <option value="ngoai_khoa">Ngoại khoa</option>
                                <option value="nhi_khoa">Nhi khoa</option>
                                <option value="san_phu_khoa">Sản phụ khoa</option>
                                <option value="tai_mui_hong">Tai - Mũi - Họng</option>
                                <option value="mat">Mắt</option>
                                <option value="da_lieu">Da liễu</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="trieu_chung" class="form-label">Lý do khám / Triệu chứng ban đầu</label>
                        <textarea class="form-control" name="trieu_chung" rows="1" placeholder="Ví dụ: Ho, sốt, đau đầu..."></textarea>
                    </div>
                </div>

                <!-- 4. Các nút điều khiển -->
                <div class="d-flex justify-content-between align-items-center mt-5">
                    <!-- Thêm class btn-lg cho nút này -->
                    <a href="dashboard_letan.php" class="btn btn-outline-secondary btn-lg d-flex align-items-center">
                        <i class="fas fa-arrow-left me-2"></i>Quay về
                    </a>

                    <div class="d-flex gap-2">
                        <!-- Thêm class btn-lg cho nút này -->
                        <button type="reset" class="btn btn-secondary btn-lg flex-fill">
                            <i class="fas fa-sync-alt me-2"></i>Làm lại
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg flex-fill">
                            <i class="fas fa-save me-2"></i>Tạo Hồ Sơ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>