<?php
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
                    <h5 class="mb-0">Chi tiết hồ sơ khám bệnh #<?= htmlspecialchars($record['data']['id']) ?></h5>
                    <div>
                        <!-- Return to patient view instead of records list -->
                        <a href="/patients/view/<?= $record['data']['patient_id'] ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại hồ sơ bệnh nhân
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Patient Information with link to patient view -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="text-muted">Thông tin bệnh nhân</h6>
                            </div>
                            <dl class="row mt-2">
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

                        <!-- Visit Information -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted">Thông tin buổi khám</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Ngày khám</dt>
                                <dd class="col-sm-8"><?= date('d/m/Y', strtotime($record['data']['ngaykham'])) ?></dd>
                                
                                <dt class="col-sm-4">Bác sĩ phụ trách</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['doctor_name']) ?></dd>
                                
                                <dt class="col-sm-4">Khoa</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($record['data']['department_name']) ?></dd>
                                
                                <dt class="col-sm-4">Ngày tái khám</dt>
                                <dd class="col-sm-8">
                                    <?php if ($record['data']['ngay_taikham']): ?>
                                        <strong class="text-primary"><?= date('d/m/Y', strtotime($record['data']['ngay_taikham'])) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">Không có lịch tái khám</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <h6 class="text-muted">Kết quả khám bệnh</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-2">Lý do khám</dt>
                                        <dd class="col-sm-10"><?= nl2br(htmlspecialchars($record['data']['lydo'])) ?></dd>
                                        
                                        <dt class="col-sm-2">Chẩn đoán</dt>
                                        <dd class="col-sm-10">
                                            <strong><?= nl2br(htmlspecialchars($record['data']['chan_doan'])) ?></strong>
                                        </dd>
                                        
                                        <?php if (!empty($record['data']['ghichu'])): ?>
                                            <dt class="col-sm-2">Ghi chú</dt>
                                            <dd class="col-sm-10"><?= nl2br(htmlspecialchars($record['data']['ghichu'])) ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prescriptions -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Đơn thuốc cho bệnh nhân</h5>
                    
                    <?php if ($_SESSION['user']['role'] === 'bacsi'): ?>
                        <a href="/prescriptions/create?record_id=<?= $record['data']['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Kê đơn thuốc mới
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (empty($record['data']['prescriptions'])): ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-pills fa-2x mb-3"></i>
                            <p>Chưa có đơn thuốc nào được kê cho buổi khám này</p>
                            
                            <?php if ($_SESSION['user']['role'] === 'bacsi'): ?>
                                <a href="/prescriptions/create?record_id=<?= $record['data']['id'] ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-plus"></i> Kê đơn thuốc
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Ngày kê đơn</th>
                                        <th>Số loại thuốc</th>
                                        <th>Trạng thái</th>
                                        <th>Dược sĩ phát thuốc</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($record['data']['prescriptions'] as $prescription): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($prescription['id']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($prescription['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= count($prescription['medicines']) ?> loại</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getPrescriptionStatusColor($prescription['status']) ?>">
                                                    <?= getPrescriptionStatusText($prescription['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($prescription['pharmacist_name']): ?>
                                                    <i class="fas fa-user-md me-1"></i> <?= htmlspecialchars($prescription['pharmacist_name']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-clock me-1"></i> Đang chờ phát thuốc</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết đơn thuốc">
                                                    <i class="fas fa-eye"></i> Chi tiết
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
        'cancelled' => 'Đã hủy'
    ];
    return $statusMap[$status] ?? $status;
}

function getPrescriptionStatusColor($status) {
    $colorMap = [
        'pending' => 'warning',
        'dispensed' => 'success',
        'cancelled' => 'danger'
    ];
    return $colorMap[$status] ?? 'secondary';
}
?>

<?php require_once '../app/views/layouts/footer.php'; ?>