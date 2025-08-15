<?php

// Bước 1: Nạp file configuration
require_once('../../configuration/configuration.php');

// Bước 2: Nạp file kiểm tra quyền
require_once(ROOT_PATH . '/configuration/access_control.php');

// Bước 3: Gọi hàm kiểm tra với vai trò được phép
check_access(['bacsi', 'letan', 'admin']); // Chỉ bác sĩ, lễ tân, admin được vào trang này

// Lấy vai trò của người dùng để sử dụng trong logic hiển thị
$user_role = $_SESSION['role'] ?? '';

// Xác định đúng trang dashboard để quay về
$dashboard_url = BASE_URL . '/pages/login.php'; // Mặc định
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin': $dashboard_url = BASE_URL . '/pages/admin/dashboard_admin.php'; break;
        case 'bacsi': $dashboard_url = BASE_URL . '/pages/bacsi/dashboard_bacsi.php'; break;
        case 'letan': $dashboard_url = BASE_URL . '/pages/letan/dashboard_letan.php'; break;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Khám và Kê đơn - Hồ sơ Bệnh án</title>
  
  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php if ($user_role === 'bacsi'): // Chỉ bác sĩ mới cần CSS của Select2 ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <?php endif; ?>
  <link rel="stylesheet" href="../../css/styles.css"> <!-- File CSS chung -->
  <style>
    /* Tùy chỉnh cho Select2 để hợp với theme Bootstrap 5 */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #dee2e6;
        padding: 0.375rem 0.75rem;
        min-height: 38px;
    }
    .select2-container {
        width: 100% !important;
    }
  </style>
</head>
<body>

<div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 py-5">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 1400px;">
        <div class="card-body">

            <!-- Header với nút Quay về động -->
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h1 class="display-6 fw-bold"><i class="fas fa-file-medical text-primary me-2"></i>Hồ sơ bệnh án: <span class="text-primary">BN-00123</span></h1>
                    <p class="h3 text-muted mb-0">Bệnh nhân: Trần Thị B</p>
                </div>
                <a href="<?= htmlspecialchars($dashboard_url) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay về Dashboard</a>
            </div>

<div class="row g-4">
    <!-- ======================================================= -->
<!-- CỘT BÊN TRÁI: THÔNG TIN CÁ NHÂN, TIỀN SỬ, VÀ LỊCH SỬ KHÁM -->
<!-- ======================================================= -->
<div class="col-lg-5">
    <!-- Phần Thông tin cá nhân (giữ nguyên logic phân quyền) -->
    <div class="mb-4">
        <form action="update_personal_info.php" method="POST">
            <input type="hidden" name="patient_id" value="BN-00123">
            
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0"><i class="fas fa-user-circle me-2"></i>Thông tin cá nhân</h3>
                <?php if ($user_role === 'letan'): // Chỉ Lễ tân mới thấy nút này ?>
                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-save me-1"></i>Lưu</button>
                <?php endif; ?>
            </div>
            <hr class="mt-2">

            <?php if ($user_role === 'letan'): // Giao diện SỬA cho Lễ tân ?>
                <div class="bg-light p-3 rounded">
                    <div class="mb-2 row"><label class="col-sm-4 col-form-label">Họ tên</label><div class="col-sm-8"><input type="text" class="form-control" name="hoten" value="Trần Thị B"></div></div>
                    <div class="mb-2 row"><label class="col-sm-4 col-form-label">Ngày sinh</label><div class="col-sm-8"><input type="date" class="form-control" name="ngaysinh" value="1985-08-15"></div></div>
                    <div class="mb-2 row"><label class="col-sm-4 col-form-label">Điện thoại</label><div class="col-sm-8"><input type="tel" class="form-control" name="sdt" value="0987654321"></div></div>
                    <div class="row"><label class="col-sm-4 col-form-label">Địa chỉ</label><div class="col-sm-8"><input type="text" class="form-control" name="diachi" value="123 Đường ABC, Quận 1, TP.HCM"></div></div>
                    <div class="row"><label class="col-sm-4 col-form-label">Email</label><div class="col-sm-8"><input type="email" class="form-control" name="email" value="tranthib@example.com"></div></div>
                </div>
            <?php else: // Giao diện CHỈ ĐỌC cho Bác sĩ và Admin ?>
                <dl class="row bg-light p-3 rounded mb-0">
                    <dt class="col-sm-4">Ngày sinh</dt><dd class="col-sm-8">15/08/1985 (39 tuổi)</dd>
                    <dt class="col-sm-4">Giới tính</dt><dd class="col-sm-8">Nữ</dd>
                    <dt class="col-sm-4">Điện thoại</dt><dd class="col-sm-8">0987654321</dd>
                    <dt class="col-sm-4">Địa chỉ</dt><dd class="col-sm-8">123 Đường ABC, Quận 1, TP.HCM</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">tranthib@example.com</dd>
                </dl>
            <?php endif; ?>
        </form>
    </div>

    <!-- Phần Tiền sử bệnh án (giữ nguyên) -->
    <div class="mb-4">
        <h3 class="fw-bold"><i class="fas fa-book-medical me-2"></i>Tiền sử bệnh án</h3>
        <hr class="mt-2">
        <div class="border rounded p-3 bg-light">
            <p><strong>Dị ứng:</strong> Không có dị ứng thuốc được ghi nhận.</p>
            <p><strong>Bệnh mãn tính:</strong> Viêm xoang, huyết áp thấp.</p>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- PHẦN MỚI: LỊCH SỬ KHÁM BỆNH (CHỈ ĐỌC)                 -->
    <!-- ======================================================= -->
    <div class="mb-4">
        <h3 class="fw-bold"><i class="fas fa-history me-2"></i>Lịch sử khám bệnh</h3>
        <hr class="mt-2">
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>Ngày Khám</th>
                        <th>Chẩn đoán</th>
                        <th>Bác sĩ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dữ liệu mẫu (sẽ được lặp bằng PHP) -->
                    <!-- Lần khám hiện tại hoặc gần nhất có thể được làm nổi bật -->
                    <tr class="table-primary">
                        <td class="text-center">25/07/2025</td>
                        <td><a href="chitiet_benhan.php?id=HSBA123" class="text-decoration-none">Viêm họng cấp</a></td>
                        <td class="text-center">BS. Trần B</td>
                    </tr>
                    <tr>
                        <td class="text-center">10/01/2025</td>
                        <td><a href="chitiet_benhan.php?id=HSBA101" class="text-decoration-none">Khám tổng quát</a></td>
                        <td class="text-center">BS. Lê C</td>
                    </tr>
                    <tr>
                        <td class="text-center">15/09/2024</td>
                        <td><a href="chitiet_benhan.php?id=HSBA088" class="text-decoration-none">Đau dạ dày</a></td>
                        <td class="text-center">BS. Trần B</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- CỘT BÊN PHẢI: KHÁM BỆNH VÀ KÊ ĐƠN                      -->
    <!-- ======================================================= -->
    <div class="col-lg-7">
        <form action="luu_benh_an_va_don_thuoc.php" method="POST">
            <input type="hidden" name="ma_hsba" value="BN-00123">

            <?php if ($user_role === 'bacsi'): // Giao diện SỬA cho Bác sĩ ?>
                <h3 class="fw-bold text-primary"><i class="fas fa-pen-to-square me-2"></i>Kết quả & Chẩn đoán</h3>
                <h5 class="text-muted mb-3"><i class="fas fa-calendar-alt me-2"></i>Ngày khám: <?= date("d/m/Y") ?></h5>
                <div class="mb-3"><label class="form-label"><strong>Chẩn đoán:</strong></label><textarea name="chandoan" class="form-control" rows="3" placeholder="Nhập chẩn đoán..."></textarea></div>
                <div class="mb-3"><label class="form-label"><strong>Yêu cầu cận lâm sàng:</strong></label><textarea name="yeucau_cls" class="form-control" rows="2" placeholder="Các yêu cầu xét nghiệm..."></textarea></div>
                <hr class="my-4">
                <h3 class="fw-bold text-primary"><i class="fas fa-file-prescription me-2"></i>Kê đơn thuốc</h3>
                <div class="mb-3"><label class="form-label"><strong>Chọn thuốc:</strong></label><select id="thuocSelect" class="form-control" multiple></select></div>
                <div id="thuocDetailsContainer"><label class="form-label mt-2 fw-bold d-none" id="thuocDetailsTitle">Chi tiết liều lượng:</label><div id="thuocDetails"></div></div>
                <hr class="my-4">
                <h3 class="fw-bold text-primary"><i class="fas fa-calendar-check me-2"></i>Kế hoạch Tái khám</h3>
                <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="henTaikhamCheck"><label class="form-check-label" for="henTaikhamCheck">Hẹn ngày tái khám</label></div>
                <div id="taikhamDetails" style="opacity: 0.5;"><div class="row g-3"><div class="col-md-6"><label class="form-label">Ngày tái khám</label><input type="date" id="ngayTaikham" name="ngay_taikham" class="form-control" disabled></div><div class="col-md-6"><label class="form-label">Ghi chú</label><textarea id="ghiChuTaikham" name="ghichu_taikham" class="form-control" rows="1" placeholder="Nội dung cần theo dõi..." disabled></textarea></div></div></div>
                <div class="text-end mt-5"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Lưu Bệnh Án và Kê Đơn</button></div>
            
                <!-- Dữ liệu mẫu (sẽ được lặp bằng PHP) -->
                <?php else: // Giao diện CHỈ ĐỌC cho Lễ tân và Admin ?>
                            <h3 class="fw-bold text-primary"><i class="fas fa-notes-medical me-2"></i>Kết quả khám bệnh</h3>
                            <!-- Backend sẽ lặp qua các lần khám và hiển thị ở đây -->
                            <div class="border p-3 rounded">
                                <h5 class="fw-bold">Lần khám ngày: 01/07/2025</h5>
                                <p><strong>Chẩn đoán:</strong> Viêm họng cấp.</p>
                                <p><strong>Đơn thuốc:</strong> Paracetamol 500mg, Amoxicillin 250mg.</p>
                                <p><strong>Tái khám:</strong> Không hẹn.</p>
                            </div>
                        <?php endif; ?>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($user_role === 'bacsi'): // Chỉ bác sĩ mới cần load các script nặng này ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // Khởi tạo Select2
    $('#thuocSelect').select2({
      placeholder: "Tìm và chọn các loại thuốc để thêm vào đơn",
      width: '100%'
    });

    // Hàm render các trường nhập liệu cho thuốc
    function syncThuocFields() {
        const container = $('#thuocDetails');
        const selectedValues = $('#thuocSelect').val() || [];

        // 1. Xóa các thuốc đã bị bỏ chọn
        container.find('.thuoc-item').each(function() {
            const drugValue = $(this).data('thuoc-value');
            if (!selectedValues.includes(drugValue)) {
                $(this).remove(); // Chỉ xóa những div không còn trong danh sách chọn
            }
        });

        // 2. Thêm các thuốc mới được chọn
        selectedValues.forEach(function(value) {
            // Kiểm tra xem thuốc đã tồn tại trong DOM chưa
            if (container.find('.thuoc-item[data-thuoc-value="' + value + '"]').length === 0) {
                const thuocText = $('#thuocSelect').find('option[value="' + value + '"]').text();
                const html = `
            <div class="card bg-light p-3 mb-2 thuoc-item" data-thuoc-value="${value}">
                <input type="hidden" name="thuoc[]" value="${value}">
                <strong class="mb-2"><i class="fas fa-capsules me-2 text-primary"></i>${thuocText}</strong>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-sm fw-semibold">Liều lượng / lần</label>
                        <input type="text" name="lieu_luong[${value}]" class="form-control form-control-sm" placeholder="VD: 1 viên" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-sm fw-semibold">Số ngày uống</label>
                        <input type="number" name="so_ngay[${value}]" class="form-control form-control-sm" placeholder="VD: 7" required>
                    </div>
                    <div class="col-md-12 d-flex align-items-center">
                        <label class="form-label-sm fw-semibold me-3 mb-0">Thời gian uống (chọn nhiều)</label>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" name="thoi_gian_uong[${value}][]" value="Sang" id="sang_${value}">
                            <label class="form-check-label" for="sang_${value}">Sáng</label>
                        </div>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" name="thoi_gian_uong[${value}][]" value="Chieu" id="chieu_${value}">
                            <label class="form-check-label" for="chieu_${value}">Chiều</label>
                        </div>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="checkbox" name="thoi_gian_uong[${value}][]" value="Toi" id="toi_${value}">
                            <label class="form-check-label" for="toi_${value}">Tối</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-sm fw-semibold">Ghi chú</label>
                        <input type="text" name="ghi_chu[${value}]" class="form-control form-control-sm" placeholder="VD: Uống sau khi ăn no, trước khi ngủ...">
                    </div>
                </div>
            </div>`;
            container.append(html);
            }
        });

        // 3. Ẩn/hiện tiêu đề "Chi tiết liều lượng"
        if(selectedValues.length > 0) {
            $('#thuocDetailsTitle').removeClass('d-none');
        } else {
            $('#thuocDetailsTitle').addClass('d-none');
        }
    }
    
    // Bắt sự kiện thay đổi của Select2 và gọi hàm đồng bộ
    $('#thuocSelect').on('change', syncThuocFields);


    // --- LOGIC CHO MỤC HẸN TÁI KHÁM ---
    $('#henTaikhamCheck').on('change', function() {
        const isChecked = $(this).is(':checked');
        const detailsDiv = $('#taikhamDetails');
        
        // Kích hoạt hoặc vô hiệu hóa các trường input
        $('#ngayTaikham, #ghiChuTaikham').prop('disabled', !isChecked);
        
        // Thêm hiệu ứng mờ để trực quan hơn
        if(isChecked) {
            detailsDiv.css('opacity', 1);
        } else {
            detailsDiv.css('opacity', 0.5);
        }
    });

  });
</script>
<?php endif; ?>

</body>
</html>