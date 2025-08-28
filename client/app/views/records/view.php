<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\views\records\view.php
require_once '../app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Hồ Sơ Khám Bệnh #<?= htmlspecialchars($record['data']['id']) ?></h5>
                    <a href="/records" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Patient Information -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted">Thông Tin Bệnh Nhân</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Họ và tên</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['patient_name']) ?></dd>
                                
                                <dt class="col-sm-4">Ngày sinh</dt>
                                <dd class="col-sm-8"><?= date('d/m/Y', strtotime($record['data']['patient_dob'])) ?></dd>
                                
                                <dt class="col-sm-4">Giới tính</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['patient_gender']) ?></dd>
                                
                                <dt class="col-sm-4">Số điện thoại</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['patient_phone'] ?? 'N/A') ?></dd>
                            </dl>
                        </div>

                        <!-- Doctor Information -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted">Thông Tin Khám Bệnh</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Ngày khám</dt>
                                <dd class="col-sm-8"><?= date('d/m/Y', strtotime($record['data']['ngaykham'])) ?></dd>
                                
                                <dt class="col-sm-4">Bác sĩ</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['doctor_name']) ?></dd>
                                
                                <dt class="col-sm-4">Khoa</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['department_name']) ?></dd>
                                
                                <dt class="col-sm-4">Ngày tái khám</dt>
                                <dd class="col-sm-8">
                                    <?= $record['data']['ngay_taikham'] ? date('d/m/Y', strtotime($record['data']['ngay_taikham'])) : 'Không có' ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h6 class="text-muted">Chi Tiết Khám Bệnh</h6>
                            <dl class="row">
                                <dt class="col-sm-2">Lý do khám</dt>
                                <dd class="col-sm-10"><?= nl2br(htmlspecialchars($record['data']['lydo'])) ?></dd>
                                
                                <dt class="col-sm-2">Chẩn đoán</dt>
                                <dd class="col-sm-10"><?= nl2br(htmlspecialchars($record['data']['chan_doan'])) ?></dd>
                                
                                <?php if (!empty($record['data']['ghichu'])): ?>
                                    <dt class="col-sm-2">Ghi chú</dt>
                                    <dd class="col-sm-10"><?= nl2br(htmlspecialchars($record['data']['ghichu'])) ?></dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prescriptions -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đơn Thuốc</h5>
                    
                    <?php if ($_SESSION['user']['role'] === 'bacsi'): ?>
                        <a href="/prescriptions/create?record_id=<?= $record['data']['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Thêm Đơn Thuốc
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($record['data']['prescriptions'])): ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-pills fa-2x mb-3"></i>
                            <p>Chưa có đơn thuốc nào</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ngày tạo</th>
                                        <th>Số loại thuốc</th>
                                        <th>Trạng thái</th>
                                        <th>Dược sĩ</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($record['data']['prescriptions'] as $prescription): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($prescription['id']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($prescription['created_at'])) ?></td>
                                            <td><?= count($prescription['medicines']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getPrescriptionStatusColor($prescription['status']) ?>">
                                                    <?= getPrescriptionStatusText($prescription['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= $prescription['pharmacist_name'] ?? 'Chưa phát thuốc' ?></td>
                                            <td>
                                                <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
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
    </div>
</div>

<?php 
// Helper functions for prescription status
function getPrescriptionStatusText($status) {
    $statusMap = [
        'pending' => 'Chờ phát thuốc',
        'dispensed' => 'Đã phát thuốc',
        'canceled' => 'Đã hủy'
    ];
    return $statusMap[$status] ?? $status;
}

function getPrescriptionStatusColor($status) {
    $colorMap = [
        'pending' => 'warning',
        'dispensed' => 'success',
        'canceled' => 'danger'
    ];
    return $colorMap[$status] ?? 'secondary';
}
?>

<?php require_once '../app/views/layouts/footer.php'; ?>