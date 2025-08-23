<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lịch Hẹn Chờ Duyệt</h2>
        <div>
            <a href="/appointments" class="btn btn-secondary">
                <i class="fas fa-calendar"></i> Tất Cả Lịch Hẹn
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Bệnh Nhân</th>
                            <th>Thời Gian Đề Xuất</th>
                            <th>Lý Do Khám</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Không có lịch hẹn nào chờ duyệt</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $apt): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($apt['patient_name']) ?></strong><br>
                                        <small class="text-muted">SĐT: <?= htmlspecialchars($apt['patient_phone']) ?></small>
                                    </td>
                                    <td>
                                        <?= (new DateTime($apt['requested_time']))->format('d/m/Y H:i') ?>
                                    </td>
                                    <td><?= htmlspecialchars($apt['lydo']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $apt['status'] === 'pending' ? 'warning' : 'info' ?>">
                                            <?= $apt['status'] === 'pending' ? 'Chờ duyệt' : 'Đã đề xuất' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-success me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#proposeModal"
                                                data-appointment-id="<?= $apt['id'] ?>"
                                                data-patient-name="<?= htmlspecialchars($apt['patient_name']) ?>">
                                            <i class="fas fa-clock"></i> Đề xuất giờ
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#declineModal"
                                                data-appointment-id="<?= $apt['id'] ?>"
                                                data-patient-name="<?= htmlspecialchars($apt['patient_name']) ?>">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Propose Time Modal -->
<div class="modal fade" id="proposeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="proposeForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Đề Xuất Thời Gian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Đề xuất thời gian khám cho bệnh nhân <strong id="patientNamePropose"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Chọn thời gian</label>
                        <input type="datetime-local" 
                               class="form-control" 
                               name="proposed_time" 
                               required
                               min="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Đề xuất</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="declineForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Từ Chối Lịch Hẹn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Từ chối lịch hẹn của bệnh nhân <strong id="patientNameDecline"></strong></p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle propose modal
    const proposeModal = document.getElementById('proposeModal');
    proposeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const appointmentId = button.dataset.appointmentId;
        const patientName = button.dataset.patientName;
        
        document.getElementById('patientNamePropose').textContent = patientName;
        document.getElementById('proposeForm').action = `/appointments/propose/${appointmentId}`;
    });

    // Handle decline modal
    const declineModal = document.getElementById('declineModal');
    declineModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const appointmentId = button.dataset.appointmentId;
        const patientName = button.dataset.patientName;
        
        document.getElementById('patientNameDecline').textContent = patientName;
        document.getElementById('declineForm').action = `/appointments/decline/${appointmentId}`;
    });
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>