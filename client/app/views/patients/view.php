<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
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

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lịch Sử Khám Bệnh</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($medicalRecords as $record): ?>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?= (new DateTime($record['ngaykham']))->format("d/m/Y") ?>
                                </div>
                                <div class="timeline-content">
                                    <h6>Bác sĩ: <?= htmlspecialchars($record['ten_bacsi']) ?></h6>
                                    <p><strong>Chẩn đoán:</strong> <?= htmlspecialchars($record['chan_doan']) ?></p>
                                    <p><strong>Đơn thuốc:</strong> <?= htmlspecialchars($record['don_thuoc']) ?></p>
                                    <p><strong>Ghi chú:</strong> <?= htmlspecialchars($record['ghi_chu']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
</style>

<?php require_once '../app/views/layouts/footer.php'; ?>