<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Patient Info Card - Left Column -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông Tin Bệnh Nhân</h5>
                </div>
                <div class="card-body">
                    <h3><?= htmlspecialchars($patient['hoten_bn']) ?></h3>
                    <p class="text-muted">ID: <?= htmlspecialchars($patient['id']) ?></p>
                    <hr>
                    <dl class="row">
                        <dt class="col-sm-4">Ngày sinh</dt>
                        <dd class="col-sm-8"><?= (new DateTime($patient['dob']))->format("d/m/Y") ?></dd>
                        
                        <dt class="col-sm-4">Giới tính</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($patient['gender']) ?></dd>
                        
                        <dt class="col-sm-4">SĐT</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($patient['sdt']) ?></dd>
                        
                        <dt class="col-sm-4">Địa chỉ</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($patient['diachi']) ?></dd>
                    </dl>
                    <a href="/patients/edit/<?= $patient['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Cập nhật thông tin
                    </a>
                </div>
            </div>
        </div>

        <!-- Medical Records Timeline - Right Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lịch Sử Khám Bệnh</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php if (empty($medicalRecords)): ?>
                            <div class="alert alert-info">
                                Chưa có lịch sử khám bệnh
                            </div>
                        <?php else: ?>
                            <?php foreach ($medicalRecords as $record): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        Ngày khám: <?= (new DateTime($record['ngaykham']))->format("d/m/Y H:i") ?>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6>
                                                Bác sĩ: <?= htmlspecialchars($record['doctor_name']) ?>
                                                <small class="text-muted">
                                                    (<?= htmlspecialchars($record['department_name']) ?>)
                                                </small>
                                            </h6>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Lý do khám:</strong> 
                                            <p><?= htmlspecialchars($record['lydo']) ?></p>
                                        </div>

                                        <?php if ($record['chan_doan']): ?>
                                            <div class="mb-3">
                                                <strong>Chẩn đoán:</strong>
                                                <p><?= htmlspecialchars($record['chan_doan']) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($record['prescriptions'])): ?>
                                            <div class="mt-3">
                                                <strong>Đơn thuốc:</strong>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Thuốc</th>
                                                                <th>Liều lượng</th>
                                                                <th>Thời gian</th>
                                                                <th>Ghi chú</th>
                                                                <th>Trạng thái</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($record['prescriptions'] as $prescription): ?>
                                                                <tr>
                                                                    <td>
                                                                        <?= htmlspecialchars($prescription['medicine']['name']) ?>
                                                                        <small class="text-muted">
                                                                            (<?= htmlspecialchars($prescription['medicine']['unit']) ?>)
                                                                        </small>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($prescription['medicine']['dosage']) ?></td>
                                                                    <td><?= htmlspecialchars($prescription['medicine']['frequency']) ?></td>
                                                                    <td><?= htmlspecialchars($prescription['medicine']['note']) ?></td>
                                                                    <td>
                                                                        <span class="badge bg-<?= $prescription['status'] === 'collected' ? 'success' : 'warning' ?>">
                                                                            <?= $prescription['status'] === 'collected' ? 'Đã phát' : 'Chờ phát' ?>
                                                                        </span>
                                                                        <?php if ($prescription['pharmacist_name']): ?>
                                                                            <br>
                                                                            <small class="text-muted">
                                                                                Dược sĩ: <?= htmlspecialchars($prescription['pharmacist_name']) ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($record['ghichu']): ?>
                                            <div class="mt-3">
                                                <strong>Ghi chú:</strong>
                                                <p class="mb-0"><?= htmlspecialchars($record['ghichu']) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($record['ngay_taikham']): ?>
                                            <div class="mt-3 text-info">
                                                <i class="fas fa-calendar-alt"></i>
                                                <strong>Ngày tái khám:</strong>
                                                <?= (new DateTime($record['ngay_taikham']))->format("d/m/Y") ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    padding: 20px;
    border-left: 2px solid #007bff;
    position: relative;
    margin-bottom: 20px;
}

.timeline-date {
    color: #6c757d;
    font-weight: bold;
    margin-bottom: 10px;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}
</style>

<?php require_once '../app/views/layouts/footer.php'; ?>