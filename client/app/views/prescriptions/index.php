<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-prescription me-2"></i> Tất cả đơn thuốc</h2>
            <p class="text-muted">Danh sách toàn bộ đơn thuốc trong hệ thống</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <?php if ($_SESSION['user']['role'] === 'duocsi'): ?>
                <a href="/prescriptions/pending" class="btn btn-outline-primary">
                    <i class="fas fa-clock me-1"></i> Chờ phát
                </a>
                <?php endif; ?>
                <a href="/prescriptions" class="btn btn-primary active">
                    <i class="fas fa-list me-1"></i> Tất cả đơn thuốc
                </a>
            </div>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Bộ lọc</h5>
        </div>
        <div class="card-body">
            <form action="/prescriptions" method="GET" class="row g-3">
                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Chờ phát</option>
                        <option value="dispensed" <?= isset($_GET['status']) && $_GET['status'] === 'dispensed' ? 'selected' : '' ?>>Đã phát</option>
                        <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                
                <!-- Date Range Filter -->
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
                
                <!-- Filter Button -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="/prescriptions" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Prescriptions List -->
    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($prescriptions)): ?>
                <div class="text-center py-5">
                    <img src="/assets/images/empty-list.svg" alt="Không có đơn thuốc" style="width: 120px;" class="mb-3">
                    <h5>Không tìm thấy đơn thuốc nào</h5>
                    <p class="text-muted">Hãy thử thay đổi bộ lọc để tìm kiếm</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Bệnh nhân</th>
                                <th>Bác sĩ</th>
                                <th>Ngày kê đơn</th>
                                <th>Trạng thái</th>
                                <th>Dược sĩ phát thuốc</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $prescription): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prescription['id']) ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($prescription['patient_name']) ?></div>
                                        <small class="text-muted">
                                            <a href="/patients/view/<?= $prescription['patient_id'] ?>" class="text-muted">
                                                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($prescription['patient_id']) ?>
                                            </a>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($prescription['doctor_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prescription['created_at'])) ?></td>
                                    <td>
                                        <?php if ($prescription['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Chờ phát</span>
                                        <?php elseif ($prescription['status'] === 'dispensed'): ?>
                                            <span class="badge bg-success">Đã phát</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Đã hủy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prescription['status'] === 'dispensed'): ?>
                                            <?= htmlspecialchars($prescription['pharmacist_name'] ?? 'N/A') ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye me-1"></i> Chi tiết
                                        </a>
                                        <?php if ($_SESSION['user']['role'] === 'duocsi' && $prescription['status'] === 'pending'): ?>
                                        <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-success ms-1">
                                            <i class="fas fa-prescription-bottle-alt me-1"></i> Phát thuốc
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