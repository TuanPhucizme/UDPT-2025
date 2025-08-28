<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-prescription-bottle-alt me-2"></i> Đơn thuốc chờ phát</h2>
            <p class="text-muted">Danh sách đơn thuốc đang chờ phát cho bệnh nhân</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="/prescriptions/pending" class="btn btn-primary active">
                    <i class="fas fa-clock me-1"></i> Chờ phát
                </a>
                <a href="/prescriptions" class="btn btn-outline-secondary">
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
    
    <div class="card shadow">
        <div class="card-body">
            <?php if (empty($prescriptions)): ?>
                <div class="text-center py-5">
                    <img src="/assets/images/empty-list.svg" alt="Không có đơn thuốc" style="width: 120px;" class="mb-3">
                    <h5>Không có đơn thuốc nào đang chờ phát</h5>
                    <p class="text-muted">Tất cả đơn thuốc đã được phát hoặc hủy</p>
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
                                <th>Số loại thuốc</th>
                                <th>Khoa</th>
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
                                            ID: <?= htmlspecialchars($prescription['patient_id']) ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($prescription['doctor_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prescription['created_at'])) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= count($prescription['medicines']) ?> loại</span>
                                    </td>
                                    <td><?= htmlspecialchars($prescription['department_name']) ?></td>
                                    <td>
                                        <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-prescription me-1"></i> Xem và phát thuốc
                                        </a>
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