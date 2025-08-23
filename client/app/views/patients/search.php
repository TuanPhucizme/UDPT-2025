<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tìm Kiếm Bệnh Nhân</h2>
        <a href="/patients" class="btn btn-secondary">Quay lại</a>
    </div>

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
                        <option value="nam" <?= ($_GET['gender'] ?? '') === 'nam' ? 'selected' : '' ?>>Nam</option>
                        <option value="nu" <?= ($_GET['gender'] ?? '') === 'nu' ? 'selected' : '' ?>>Nữ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số Điện Thoại</label>
                    <input type="text" name="phone" class="form-control" 
                           value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tuổi</label>
                    <div class="input-group">
                        <input type="number" 
                               name="age" 
                               class="form-control" 
                               min="0"
                               max="150"
                               value="<?= htmlspecialchars($_GET['age'] ?? '') ?>"
                               placeholder="VD: 25">
                        <span class="input-group-text">tuổi</span>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm Kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ Tên</th>
                    <th>Ngày Sinh</th>
                    <th>Tuổi</th>
                    <th>Giới Tính</th>
                    <th>Số Điện Thoại</th>
                    <th>Địa Chỉ</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Không tìm thấy kết quả phù hợp</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td><?= htmlspecialchars($patient['id']) ?></td>
                            <td><?= htmlspecialchars($patient['hoten_bn']) ?></td>
                            <td><?= (new DateTime($patient['dob']))->format("d/m/Y") ?></td>
                            <td><?= date_diff(new DateTime($patient['dob']), new DateTime())->y ?></td>
                            <td><?= htmlspecialchars($patient['gender']) ?></td>
                            <td><?= htmlspecialchars($patient['sdt']) ?></td>
                            <td><?= htmlspecialchars($patient['diachi']) ?></td>
                            <td>
                                <a href="/patients/view/<?= $patient['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/patients/edit/<?= $patient['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>