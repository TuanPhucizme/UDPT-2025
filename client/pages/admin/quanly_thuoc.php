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
  <title>Quản lý Thuốc và Kho</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/styles.css"> <!-- File CSS chung -->
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 p-3 p-md-5">
    <div class="card shadow-lg p-4 w-100 card-blur">
        <div class="card-body">

            <!-- 1. Header -->
            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold"><i class="fas fa-capsules me-2"></i>Quản lý Thuốc và Kho</h1>
                <p class="text-muted">Thêm mới, nhập kho, và theo dõi số lượng tồn kho của thuốc.</p>
            </div>

            <!-- 2. Thanh công cụ -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 p-3 bg-light rounded">
                <div class="d-flex gap-2 mb-2 mb-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMedicineModal">
                        <i class="fas fa-plus-circle me-2"></i>Thêm Thuốc Mới
                    </button>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addStockModal">
                        <i class="fas fa-truck-loading me-2"></i>Nhập Kho Thuốc Cũ
                    </button>
                </div>
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" class="form-control" placeholder="Tìm theo tên hoặc mã thuốc...">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <!-- 3. Bảng danh sách thuốc -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Mã Thuốc</th>
                            <th>Tên Thuốc</th>
                            <th>Đơn vị tính</th>
                            <th>Tồn Kho</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dữ liệu mẫu -->
                        <tr>
                            <td class="text-center">PARA500</td>
                            <td>Paracetamol 500mg</td>
                            <td class="text-center">Viên</td>
                            <td class="text-center fw-bold">1500</td>
                            <td class="text-center"><span class="badge bg-success">Còn hàng</span></td>
                            <td class="text-center"><button class="btn btn-warning btn-sm" title="Sửa"><i class="fas fa-pencil-alt"></i></button> <button class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-center">AMO250</td>
                            <td>Amoxicillin 250mg</td>
                            <td class="text-center">Viên</td>
                            <td class="text-center fw-bold">85</td>
                            <td class="text-center"><span class="badge bg-warning text-dark">Sắp hết</span></td>
                            <td class="text-center"><button class="btn btn-warning btn-sm" title="Sửa"><i class="fas fa-pencil-alt"></i></button> <button class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                        <tr>
                            <td class="text-center">IBU400</td>
                            <td>Ibuprofen 400mg</td>
                            <td class="text-center">Viên</td>
                            <td class="text-center fw-bold">0</td>
                            <td class="text-center"><span class="badge bg-danger">Hết hàng</span></td>
                            <td class="text-center"><button class="btn btn-warning btn-sm" title="Sửa"><i class="fas fa-pencil-alt"></i></button> <button class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4"><a href="dashboard_admin.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay về Dashboard</a></div>
        </div>
    </div>
</div>

<!-- Modal 1: Thêm Thuốc Mới -->
<div class="modal fade" id="newMedicineModal" tabindex="-1" aria-labelledby="newMedicineModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="newMedicineModalLabel"><i class="fas fa-plus-circle me-2"></i>Thêm Loại Thuốc Mới</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form>
            <div class="mb-3"><label class="form-label">Tên thuốc</label><input type="text" class="form-control" required></div>
            <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Mã thuốc</label><input type="text" class="form-control" required></div><div class="col-md-6 mb-3"><label class="form-label">Đơn vị tính</label><input type="text" class="form-control" placeholder="Viên, Vỉ, Hộp..." required></div></div>
            <div class="mb-3"><label class="form-label">Nhà cung cấp</label><input type="text" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Số lượng nhập ban đầu</label><input type="number" class="form-control" required></div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button><button type="button" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu thuốc mới</button></div>
    </div>
  </div>
</div>

<!-- Modal 2: Nhập Kho Thuốc Cũ -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="addStockModalLabel"><i class="fas fa-truck-loading me-2"></i>Nhập Kho Thuốc Đã Có</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form>
            <div class="mb-3">
                <label class="form-label">Chọn thuốc cần nhập kho</label>
                <select class="form-select" required>
                    <option selected disabled value="">-- Tìm và chọn thuốc --</option>
                    <option value="1">Paracetamol 500mg (Tồn: 1500)</option>
                    <option value="2">Amoxicillin 250mg (Tồn: 85)</option>
                    <option value="3">Ibuprofen 400mg (Tồn: 0)</option>
                </select>
            </div>
            <div class="mb-3"><label class="form-label">Số lượng nhập thêm</label><input type="number" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Ghi chú lô hàng (nếu có)</label><input type="text" class="form-control"></div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button><button type="button" class="btn btn-primary"><i class="fas fa-dolly-flatbed me-2"></i>Xác nhận Nhập Kho</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>