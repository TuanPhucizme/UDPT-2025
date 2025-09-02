<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-prescription me-2 text-primary"></i> Quản lý đơn thuốc</h2>
            <p class="text-muted mb-0">Danh sách toàn bộ đơn thuốc trong hệ thống</p>
        </div>
        <div>
            <div class="btn-group">
                <?php if ($_SESSION['user']['role'] === 'duocsi'): ?>
                    <a href="/prescriptions/pending" class="btn btn-outline-primary">
                        <i class="fas fa-clock me-1"></i> Chờ phát
                    </a>
                <?php endif; ?>
                <div>
                    <a href="/prescriptions" class="btn btn-primary shadow-sm px-3 me-2">
                        <i class="fas fa-list me-1"></i> Tất cả
                    </a>
                    <a href="/" class="btn btn-light shadow-sm px-3 me-2">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="fas fa-filter me-2 text-secondary"></i> Bộ lọc tìm kiếm</h5>
        </div>
        <div class="card-body">
            <form action="/prescriptions" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Chờ phát</option>
                        <option value="dispensed" <?= isset($_GET['status']) && $_GET['status'] === 'dispensed' ? 'selected' : '' ?>>Đã phát</option>
                        <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                           value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Lọc
                    </button>
                    <a href="/prescriptions" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescription List -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (empty($prescriptions)): ?>
                <div class="text-center py-5">
                    <img src="/assets/images/empty-list.svg" alt="Không có đơn thuốc" style="width: 150px;" class="mb-3">
                    <h5 class="fw-bold">Không tìm thấy đơn thuốc nào</h5>
                    <p class="text-muted">Hãy thử thay đổi bộ lọc để tìm kiếm</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Bệnh nhân</th>
                                <th>Bác sĩ</th>
                                <th>Ngày kê đơn</th>
                                <th>Trạng thái</th>
                                <th>Dược sĩ phát thuốc</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $prescription): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?= htmlspecialchars($prescription['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($prescription['patient_name']) ?></div>
                                        <small class="text-muted">
                                            <a href="/patients/view/<?= $prescription['patient_id'] ?>" class="text-decoration-none">
                                                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($prescription['patient_id']) ?>
                                            </a>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($prescription['doctor_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prescription['created_at'])) ?></td>
                                    <td>
                                        <?php if ($prescription['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i> Chờ phát</span>
                                        <?php elseif ($prescription['status'] === 'dispensed'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Đã phát</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Đã hủy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prescription['status'] === 'dispensed'): ?>
                                            <?= htmlspecialchars($prescription['pharmacist_name'] ?? 'N/A') ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($_SESSION['user']['role'] === 'duocsi' && $prescription['status'] === 'pending'): ?>
                                            <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-success ms-1" title="Phát thuốc">
                                                <i class="fas fa-prescription-bottle-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
