<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh Sách Lịch Hẹn</h2>
        <div>
            <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                <a href="/appointments/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo Lịch Hẹn
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="keyword" 
                           placeholder="Tên bệnh nhân..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date"
                           value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bệnh Nhân</th>
                    <th>Bác Sĩ</th>
                    <th>Khoa</th>
                    <th>Thời Gian</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có lịch hẹn nào</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><?= htmlspecialchars($apt['id']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($apt['patient_name']) ?></strong><br>
                                <small class="text-muted">SĐT: <?= htmlspecialchars($apt['patient_phone']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($apt['department_name']) ?></td>
                            <td><?= (new DateTime($apt['thoi_gian_hen']))->format('d/m/Y H:i') ?></td>
                            <td>
                                <span class="badge bg-<?= getBadgeColor($apt['status']) ?>">
                                    <?= getStatusText($apt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/appointments/view/<?= $apt['id'] ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($apt['status'] === 'pending' && $_SESSION['user']['role'] === 'bacsi'): ?>
                                    <a href="/appointments/pending" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-clock"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function getBadgeColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending': return 'Chờ duyệt';
        case 'confirmed': return 'Đã xác nhận';
        case 'cancelled': return 'Đã hủy';
        default: return 'Không xác định';
    }
}
?>

<?php require_once '../app/views/layouts/footer.php'; ?>