<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">
                <i class="fas fa-prescription-bottle-alt me-2 text-primary"></i> Đơn thuốc chờ phát
            </h2>
            <p class="text-muted mb-0">Danh sách đơn thuốc đang chờ phát cho bệnh nhân</p>
        </div>
        <div class="d-flex">
            <a href="/prescriptions/pending" class="btn btn-primary shadow-sm px-3 me-2">
                <i class="fas fa-clock me-1"></i> Chờ phát
            </a>
            <a href="/prescriptions" class="btn btn-outline-secondary shadow-sm px-3 me-2">
                <i class="fas fa-list me-1"></i> Tất cả đơn thuốc
            </a>
            <a href="/" class="btn btn-outline-dark shadow-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Prescription List -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (empty($prescriptions)): ?>
                <div class="text-center py-5">
                    <img src="/assets/images/empty-list.svg" alt="Không có đơn thuốc" style="width: 120px;" class="mb-3">
                    <h5 class="fw-bold">Không có đơn thuốc nào đang chờ phát</h5>
                    <p class="text-muted">Tất cả đơn thuốc đã được phát hoặc hủy</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Bệnh nhân</th>
                                <th>Bác sĩ</th>
                                <th>Ngày kê đơn</th>
                                <th>Số loại thuốc</th>
                                <th>Khoa</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $prescription): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?= htmlspecialchars($prescription['id']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($prescription['patient_name']) ?></div>
                                        <small class="text-muted">ID: <?= htmlspecialchars($prescription['patient_id']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($prescription['doctor_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($prescription['created_at'])) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= count($prescription['medicines']) ?> loại
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($prescription['department_name']) ?></td>
                                    <td class="text-center">
                                        <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-prescription me-1"></i> Xem & phát thuốc
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
