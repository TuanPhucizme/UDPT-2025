<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thêm Bệnh Nhân Mới</h5>
                    <a href="/patients" class="btn btn-secondary btn-sm">Quay lại</a>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/patients/create" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="hoten_bn" class="form-label">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="hoten_bn" name="hoten_bn" required>
                            <div class="invalid-feedback">Vui lòng nhập họ tên</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dob" class="form-label">Ngày Sinh <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dob" name="dob" 
                                       max="<?= date('Y-m-d') ?>" required>
                                <div class="invalid-feedback">Vui lòng chọn ngày sinh</div>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Giới Tính <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Chọn giới tính</option>
                                    <option value="nam">Nam</option>
                                    <option value="nu">Nữ</option>
                                    <option value="khac">Khác</option>
                                </select>
                                <div class="invalid-feedback">Vui lòng chọn giới tính</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sdt" class="form-label">Số Điện Thoại</label>
                            <input type="tel" class="form-control" id="sdt" name="sdt" 
                                   pattern="[0-9]{10,11}" title="Số điện thoại từ 10-11 số">
                            <div class="form-text">Định dạng: 10-11 số</div>
                        </div>

                        <div class="mb-3">
                            <label for="diachi" class="form-label">Địa Chỉ</label>
                            <textarea class="form-control" id="diachi" name="diachi" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tiensu_benh" class="form-label">Tiền Sử Bệnh</label>
                            <textarea class="form-control" id="tiensu_benh" name="tiensu_benh" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="lichsu_kham" class="form-label">Lịch Sử Khám</label>
                            <textarea class="form-control" id="lichsu_kham" name="lichsu_kham" rows="3"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm Bệnh Nhân
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>