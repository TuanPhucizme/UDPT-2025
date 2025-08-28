<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi Tiết Lịch Hẹn #<?= htmlspecialchars($appointment['id']) ?></h5>
                    <a href="/appointments" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="row g-4">
                        <!-- Patient Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Thông Tin Bệnh Nhân</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Họ tên</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($appointment['patient_name']) ?></dd>
                                
                                <dt class="col-sm-4">Số điện thoại</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($appointment['patient_phone'] ?? 'Không có') ?></dd>
                            </dl>
                        </div>

                        <!-- Doctor Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Thông Tin Bác Sĩ</h6>
                            <dl class="row">
                                <dt class="col-sm-4">Bác sĩ</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($appointment['doctor_name']) ?></dd>
                                
                                <dt class="col-sm-4">Khoa</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($appointment['department_name']) ?></dd>
                            </dl>
                        </div>

                        <!-- Appointment Details -->
                        <div class="col-12">
                            <h6 class="text-muted mb-3">Chi Tiết Lịch Hẹn</h6>
                            <dl class="row">
                                <dt class="col-sm-3">Thời gian hẹn</dt>
                                <dd class="col-sm-9">
                                    <?= (new DateTime($appointment['thoi_gian_hen']))->format('d/m/Y H:i') ?>
                                </dd>
                                
                                <dt class="col-sm-3">Trạng thái</dt>
                                <dd class="col-sm-9">
                                    <span class="badge bg-<?= $appointment['status_color'] ?>">
                                        <?= $appointment['status_text'] ?>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-3">Lý do khám</dt>
                                <dd class="col-sm-9"><?= nl2br(htmlspecialchars($appointment['lydo'])) ?></dd>
                                
                                <?php if (!empty($appointment['note'])): ?>
                                    <dt class="col-sm-3">Ghi chú</dt>
                                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($appointment['note'])) ?></dd>
                                <?php endif; ?>
                                
                                <dt class="col-sm-3">Ngày tạo</dt>
                                <dd class="col-sm-9">
                                    <?= (new DateTime($appointment['created_at']))->format('d/m/Y H:i') ?>
                                </dd>
                            </dl>
                        </div>

                        <!-- Medical Record Button -->
                        <?php if ($appointment['status'] === 'confirmed' && $_SESSION['user']['role'] === 'bacsi'): ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> Khám và kê đơn</h6>
                                            <p class="mb-0 small">Bạn có thể tạo hồ sơ khám bệnh và kê đơn thuốc cho lịch hẹn này</p>
                                        </div>
                                        <a href="/records/create?appointment_id=<?= $appointment['id'] ?>" class="btn btn-primary">
                                            <i class="fas fa-notes-medical"></i> Tạo hồ sơ khám bệnh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="col-12">
                            <hr>
                            <div class="d-flex gap-2 justify-content-end">
                                <?php if ($appointment['status'] === 'pending' && $_SESSION['user']['role'] === 'bacsi'): ?>
                                    <button type="button" 
                                            class="btn btn-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmModal">
                                        <i class="fas fa-check"></i> Xác nhận
                                    </button>
                                    <button type="button"
                                            class="btn btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#declineModal">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<?php if ($appointment['status'] === 'pending' && $_SESSION['user']['role'] === 'bacsi'): ?>
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/appointments/confirm/<?= $appointment['id'] ?>" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác Nhận Lịch Hẹn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xác nhận lịch hẹn này?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-success">Xác nhận</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Decline Modal -->
    <div class="modal fade" id="declineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/appointments/cancel/<?= $appointment['id'] ?>" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Từ Chối Lịch Hẹn</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Lý do từ chối</label>
                            <textarea class="form-control" name="reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-danger">Từ chối</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../app/views/layouts/footer.php'; ?>