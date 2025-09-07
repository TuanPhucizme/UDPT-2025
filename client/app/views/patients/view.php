<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <!-- Patient Info Card - Left Column -->
        <div class="col-md-4">
            <div class="card mb-4">
                <!-- Card header with back button -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Thông Tin Bệnh Nhân</h5>
                    <a href="/patients" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Danh sách
                    </a>
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
                    
                    <!-- Add action buttons -->
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                        <a href="/patients/edit/<?= $patient['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Cập nhật thông tin
                        </a>
                        <?php endif; ?>
                        <?php if ($_SESSION['user']['role'] === 'bacsi'): ?>
                        <a href="/records/create?patient_id=<?= $patient['id'] ?>" class="btn btn-success">
                            <i class="fas fa-notes-medical"></i> Tạo hồ sơ khám bệnh
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user']['role'] === 'letan'): ?>
                        <a href="/appointments/create?patient_id=<?= $patient['id'] ?>" class="btn btn-info">
                            <i class="fas fa-calendar-plus"></i> Đặt lịch hẹn
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Add a new card for quick navigation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Truy Cập Nhanh</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php if (!empty($medicalRecords)): ?>
                            <!-- Show last 3 medical records for quick navigation -->
                            <div class="mb-2 fw-bold">Hồ sơ gần đây:</div>
                            <?php 
                            $recentRecords = array_slice($medicalRecords, 0, 3);
                            foreach($recentRecords as $record): 
                            ?>
                                <a href="/records/view/<?= $record['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file-medical me-2"></i>
                                        <?= (new DateTime($record['ngaykham']))->format("d/m/Y") ?>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">
                                        <?= htmlspecialchars($record['department_name']) ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Add appointment history link if applicable -->
                        <a href="/appointments/index?patient_id=<?= $patient['id'] ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-calendar me-2"></i> Lịch sử lịch hẹn
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Records Timeline - Right Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Lịch Sử Khám Bệnh</h5>
                    
                    <?php if ($_SESSION['user']['role'] === 'bacsi'): ?>
                    <a href="/records/create?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Khám mới
                    </a>
                    <?php endif; ?>
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
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>
                                                Ngày khám: <?= (new DateTime($record['ngaykham']))->format("d/m/Y H:i") ?>
                                            </span>
                                            <a href="/records/view/<?= $record['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-medical"></i> Chi tiết hồ sơ
                                            </a>
                                        </div>
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
                                                <?php foreach ($record['prescriptions'] as $prescription): ?>
                                                    <div class="card mt-2 mb-3 border-light">
                                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="badge bg-<?= $prescription['status'] === 'dispensed' ? 'success' : ($prescription['status'] === 'pending' ? 'warning' : 'danger')?>">
                                                                    <?= $prescription['status'] === 'dispensed' ? 'Đã phát' : ($prescription['status'] === 'pending' ? 'Chờ phát' :'Đã hủy') ?>
                                                                </span>
                                                                <small class="ms-2">Ngày kê: <?= date('d/m/Y H:i', strtotime($prescription['created_at'])) ?></small>
                                                            </div>
                                                            <a href="/prescriptions/view/<?= $prescription['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> Chi tiết
                                                            </a>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm mb-0">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Thuốc</th>
                                                                            <th>Liều lượng</th>
                                                                            <th>Tần suất</th>
                                                                            <th>Thời gian</th>
                                                                            <th>Ghi chú</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($prescription['medicines'] as $medicine): ?>
                                                                        <tr>
                                                                            <td>
                                                                                <?= htmlspecialchars($medicine['name']) ?>
                                                                            </td>
                                                                            <td><?= htmlspecialchars($medicine['dosage']) ?></td>
                                                                            <td><?= htmlspecialchars($medicine['frequency']) ?></td>
                                                                            <td><?= htmlspecialchars($medicine['duration']) ?></td>
                                                                            <td><?= htmlspecialchars($medicine['note'] ?? '') ?></td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer bg-white">
                                                            <?php if ($prescription['status'] === 'dispensed' && $prescription['pharmacist_name']): ?>
                                                                <small class="text-muted">
                                                                    Dược sĩ phát thuốc: <?= htmlspecialchars($prescription['pharmacist_name']) ?> 
                                                                    (<?= date('d/m/Y H:i', strtotime($prescription['updated_at'] ?? $prescription['created_at'])) ?>)
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (empty($record['prescriptions']) && $_SESSION['user']['role'] === 'bacsi'): ?>
                                            <div class="mt-3">
                                                <a href="/prescriptions/create?record_id=<?= $record['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-prescription-bottle-alt"></i> Kê đơn thuốc
                                                </a>
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