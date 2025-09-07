<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cập Nhật Thông Tin Bệnh Nhân</h5>
                    <a href="/patients" class="btn btn-secondary btn-sm">Quay lại</a>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/patients/edit/<?= $patient['id'] ?>">
                        <div class="mb-3">
                            <label for="hoten_bn" class="form-label">Họ Tên</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="hoten_bn" 
                                   name="hoten_bn" 
                                   value="<?= htmlspecialchars($patient['hoten_bn']) ?>"
                                   required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dob" class="form-label">Ngày Sinh</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="dob" 
                                       name="dob" 
                                       value="<?php echo (new DateTime($patient['dob']))->format('Y-m-d') ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Giới Tính</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="nam" <?= $patient['gender'] === 'nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="nu" <?= $patient['gender'] === 'nu' ? 'selected' : '' ?>>Nữ</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sdt" class="form-label">Số Điện Thoại</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="sdt" 
                                   name="sdt" 
                                   value="<?= htmlspecialchars($patient['sdt']) ?>"
                                   <?php if (preg_match('/^x+\d{3}$/', $patient['sdt'])): ?>
                                   placeholder="Nhập số điện thoại mới hoặc để trống để giữ nguyên"
                                   pattern="[0-9]{10,11}|^x+\d{3}$"
                                   title="Để trống để giữ số cũ hoặc nhập số điện thoại mới (10-11 chữ số)"
                                   <?php else: ?>
                                   pattern="[0-9]{10,11}"
                                   title="Số điện thoại phải có 10-11 chữ số"
                                   required
                                   <?php endif; ?>>
                            <?php if (preg_match('/^x+\d{3}$/', $patient['sdt'])): ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Số điện thoại hiện tại đã được mã hóa để bảo mật. Để thay đổi, hãy nhập số mới, hoặc để trống để giữ số hiện tại.
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="diachi" class="form-label">Địa Chỉ</label>
                            <textarea class="form-control" 
                                      id="diachi" 
                                      name="diachi" 
                                      rows="3"><?= htmlspecialchars($patient['diachi']) ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>