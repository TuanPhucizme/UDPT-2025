<?php

// Bước 1: Nạp file config
require_once('../../configuration/config.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['admin']); // Chỉ admin được vào trang này
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Nhân viên</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../css/styles.css"> <!-- File CSS chung -->
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 p-3 p-md-5">
    <div class="card shadow-lg p-4 w-100 card-blur">
        <div class="card-body">

            <!-- 1. Header -->
            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold"><i class="fas fa-users-cog me-2"></i>Quản lý Nhân viên</h1>
                <p class="text-muted">Thêm, sửa, xóa và tra cứu thông tin nhân viên trong hệ thống.</p>
            </div>

            <!-- Thanh công cụ -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                <button class="btn btn-primary mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#employeeFormModal">
                    <i class="fas fa-user-plus me-2"></i>Thêm Nhân viên Mới
                </button>
                <div class="input-group" style="max-width: 400px;"><input type="text" class="form-control" placeholder="Tìm theo tên, chức vụ hoặc ID..."><button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button></div>
            </div>

            <!-- 3. Bảng danh sách nhân viên -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-primary text-center">
                        <tr><th>ID</th><th>Họ tên</th><th>Chức vụ</th><th>Ngày bắt đầu</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">BS001</td><td>Nguyễn Văn An</td><td class="text-center">Bác sĩ</td><td class="text-center">15/08/2020</td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal" title="Xem chi tiết"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">LT001</td><td>Trần Thị Bình</td><td class="text-center">Lễ tân</td><td class="text-center">01/03/2022</td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal" title="Xem chi tiết"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
            
            <!-- Nút quay về -->
            <div class="text-center mt-2">
                <a href="dashboard_admin.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay về Dashboard</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: XEM CHI TIẾT Nhân viên (Read-only) -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="viewModalTitle"><i class="fas fa-id-card me-2"></i>Thông tin Chi tiết Nhân viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <dl class="row">
            <dt class="col-sm-4">ID Nhân viên</dt><dd class="col-sm-8">BS001</dd>
            <dt class="col-sm-4">Họ tên</dt><dd class="col-sm-8">Nguyễn Văn An</dd>
            <dt class="col-sm-4">Ngày sinh</dt><dd class="col-sm-8">15/05/1980</dd>
            <dt class="col-sm-4">Chức vụ</dt><dd class="col-sm-8">Bác sĩ</dd>
            <dt class="col-sm-4">Chuyên khoa</dt><dd class="col-sm-8">Nội khoa</dd>
            <!-- THÔNG TIN ĐÃ THAY ĐỔI -->
            <dt class="col-sm-4">Ngày bắt đầu làm việc</dt><dd class="col-sm-8">15/08/2020</dd>
            <dt class="col-sm-4">Ngày kết thúc</dt><dd class="col-sm-8">(Còn làm việc)</dd>
            <hr class="mt-2">
            <dt class="col-sm-4">Email</dt><dd class="col-sm-8">annv@hospital.com</dd>
            <dt class="col-sm-4">Số điện thoại</dt><dd class="col-sm-8">0901234567</dd>
        </dl>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-danger" onclick="return confirm('Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa nhân viên này?')"><i class="fas fa-trash-alt me-2"></i>Xóa Nhân viên</button>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-warning" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#employeeFormModal"><i class="fas fa-pencil-alt me-2"></i>Sửa thông tin</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal 2: FORM để Thêm/Sửa Nhân viên -->
<div class="modal fade" id="employeeFormModal" tabindex="-1" aria-labelledby="formModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="formModalTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="employeeForm">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Họ tên</label><input type="text" class="form-control" name="hoten" required></div>
                <div class="col-md-6"><label class="form-label">Ngày sinh</label><input type="date" class="form-control" name="ngaysinh" required></div>
                <div class="col-md-6"><label class="form-label">Giới tính</label><select class="form-select" name="gioitinh"><option value="Nam">Nam</option><option value="Nữ">Nữ</option></select></div>
                <div class="col-md-6"><label class="form-label">Số điện thoại</label><input type="tel" class="form-control" name="sdt" required></div>
                <div class="col-md-12"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                <div class="col-md-12"><label class="form-label">Địa chỉ</label><input type="text" class="form-control" name="diachi"></div>
                <hr>
                <div class="col-md-6"><label class="form-label">Chức vụ</label><select class="form-select" name="chucvu"><option value="letan">Lễ tân</option><option value="duocsi">Dược sĩ</option><option value="bacsi">Bác sĩ</option><option value="admin">Quản trị viên</option></select></div>
                
                <!-- TRƯỜNG "TRẠNG THÁI" ĐÃ ĐƯỢC THAY THẾ -->
                <div class="col-md-6" id="startDateWrapper"><label class="form-label">Ngày bắt đầu làm việc</label><input type="date" class="form-control" name="ngay_bat_dau" required></div>
                
                <div class="col-12 d-none" id="chuyenKhoaWrapper"><label class="form-label">Chuyên khoa</label><select class="form-select" name="chuyenkhoa"><option value="" disabled selected>-- Chọn chuyên khoa --</option><option value="noi_khoa">Nội khoa</option></select></div>
                <div class="col-md-6"><label class="form-label">Tên đăng nhập</label><input type="text" class="form-control" name="username" required></div>
                <div class="col-md-6"><label class="form-label">Mật khẩu</label><input type="password" class="form-control" name="password" placeholder="Để trống nếu không muốn thay đổi"></div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu thông tin</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const employeeFormModal = document.getElementById('employeeFormModal');
    const employeeForm = document.getElementById('employeeForm');
    const formTitle = document.getElementById('formModalTitle');
    
    const chucvuSelect = employeeForm.querySelector('select[name="chucvu"]');
    const chuyenKhoaWrapper = document.getElementById('chuyenKhoaWrapper');
    
    // Hàm ẩn/hiện trường Chuyên khoa (vẫn giữ nguyên)
    function toggleChuyenKhoaField() {
        chuyenKhoaWrapper.classList.toggle('d-none', chucvuSelect.value !== 'bacsi');
    }
    chucvuSelect.addEventListener('change', toggleChuyenKhoaField);

    // Xử lý khi modal FORM được mở
    employeeFormModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const isEditMode = button.innerHTML.includes('Sửa thông tin');

        if (isEditMode) {
            formTitle.innerHTML = '<i class="fas fa-pencil-alt me-2"></i>Chỉnh sửa Thông tin Nhân viên';
            // TODO (Backend): Lấy ID nhân viên và điền dữ liệu vào form.
        } else {
            formTitle.innerHTML = '<i class="fas fa-user-plus me-2"></i>Thêm Nhân viên Mới';
            employeeForm.reset(); // Xóa sạch form
        }

        // Luôn gọi hàm này để đảm bảo trường chuyên khoa hiển thị đúng
        toggleChuyenKhoaField();
    });
});
</script>