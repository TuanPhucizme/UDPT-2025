<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">Tìm Kiếm Bệnh Nhân</h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/patients/search" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Họ Tên</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giới Tính</label>
                    <select name="gender" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="Nam" <?= ($_GET['gender'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                        <option value="Nữ" <?= ($_GET['gender'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số Điện Thoại</label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm Kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <!-- ...existing table header... -->
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Không tìm thấy kết quả phù hợp</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $patient): ?>
                        <!-- ...existing patient row code... -->
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>