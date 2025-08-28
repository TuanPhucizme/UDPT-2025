<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 me-3">
                                <i class="fas fa-prescription me-2"></i>
                                Chi tiết đơn thuốc #<?= $prescription['data']['id'] ?>
                            </h5>
                            
                            <!-- Inline status indicator -->
                            <?php if ($prescription['data']['status'] === 'dispensed'): ?>
                                <span class="badge bg-success d-flex align-items-center">
                                    <i class="fas fa-check-circle me-1"></i> Đã phát thuốc
                                </span>
                            <?php elseif ($prescription['data']['status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark d-flex align-items-center">
                                    <i class="fas fa-clock me-1"></i> Chờ phát thuốc
                                </span>
                            <?php elseif ($prescription['data']['status'] === 'cancelled'): ?>
                                <span class="badge bg-danger d-flex align-items-center">
                                    <i class="fas fa-ban me-1"></i> Đã hủy
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <?php if ($_SESSION['user']['role'] === 'duocsi'): ?>
                                <a href="/prescriptions/pending" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                                </a>
                            <?php else: ?>
                                <a href="/records/view/<?= $prescription['data']['record_id'] ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Quay lại hồ sơ
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Status details below info tables -->
                    <div class="row mb-4">
                        <!-- Patient Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Thông tin bệnh nhân</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 35%">Họ và tên</th>
                                    <td>
                                        <?= htmlspecialchars($prescription['data']['patient_name']) ?>
                                        <a href="/patients/view/<?= $prescription['data']['patient_id'] ?>" class="ms-2 text-primary">
                                            <i class="fas fa-external-link-alt small"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Bác sĩ kê đơn</th>
                                    <td><?= htmlspecialchars($prescription['data']['doctor_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Khoa</th>
                                    <td><?= htmlspecialchars($prescription['data']['department_name']) ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Prescription Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Thông tin đơn thuốc</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 35%">Mã đơn thuốc</th>
                                    <td><?= htmlspecialchars($prescription['data']['id']) ?></td>
                                </tr>
                                <tr>
                                    <th>Ngày kê đơn</th>
                                    <td><?= date('d/m/Y H:i', strtotime($prescription['data']['created_at'])) ?></td>
                                </tr>
                                
                                <tr>
                                    <th>Hồ sơ khám</th>
                                    <td>
                                        #<?= htmlspecialchars($prescription['data']['record_id']) ?>
                                        <a href="/records/view/<?= $prescription['data']['record_id'] ?>" class="ms-2 text-primary">
                                            <i class="fas fa-external-link-alt small"></i>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Medications Table -->
                    <h6 class="text-muted mb-3">Danh sách thuốc</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 30%">Tên thuốc</th>
                                    <th style="width: 15%">Liều dùng</th>
                                    <th style="width: 15%">Tần suất</th>
                                    <th style="width: 15%">Thời gian dùng</th>
                                    <th style="width: 20%">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescription['data']['medicines'] as $index => $medicine): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($medicine['name']) ?></strong>
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
                
                <!-- Additional card footer for pharmacist instructions -->
                <?php if ($_SESSION['user']['role'] === 'duocsi' && $prescription['data']['status'] === 'pending'): ?>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted"><i class="fas fa-info-circle me-1"></i> Vui lòng kiểm tra kỹ thông tin thuốc trước khi phát cho bệnh nhân</span>
                        </div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#dispenseModal">
                            <i class="fas fa-pills me-1"></i> Phát thuốc
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Medical Record Information -->
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i> Thông tin khám bệnh</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted">Chẩn đoán</h6>
                        <p class="mb-0 p-2 bg-light rounded">
                            <?= nl2br(htmlspecialchars($prescription['data']['diagnosis'] ?? 'Không có thông tin')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dispense Confirmation Modal -->
<?php if ($_SESSION['user']['role'] === 'duocsi' && $prescription['data']['status'] === 'pending'): ?>
<div class="modal fade" id="dispenseModal" tabindex="-1" aria-labelledby="dispenseModalLabel" aria-hidden="true" data-bs-show="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dispenseModalLabel">Xác nhận phát thuốc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xác nhận phát thuốc cho bệnh nhân <strong><?= htmlspecialchars($prescription['data']['patient_name']) ?></strong>?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Thao tác này sẽ cập nhật số lượng thuốc trong kho và không thể hoàn tác.
                </div>
                <div class="small text-muted">
                    <p class="mb-1"><i class="fas fa-list-ul me-1"></i> Danh sách thuốc:</p>
                    <ul>
                        <?php foreach ($prescription['data']['medicines'] as $medicine): ?>
                        <li><?= htmlspecialchars($medicine['name']) ?> - <?= htmlspecialchars($medicine['dosage']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="/prescriptions/dispense/<?= $prescription['data']['id'] ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Xác nhận phát thuốc
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add this script before the footer inclusion -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent modal from showing on page load
    const dispenseModalElement = document.getElementById('dispenseModal');
    
    if (dispenseModalElement) {
        // Manually initialize the modal with options to prevent auto-show
        const dispenseModal = new bootstrap.Modal(dispenseModalElement, {
            backdrop: 'static',
            keyboard: false,
            show: false // This ensures the modal won't show on initialization
        });
        
        // Stop modal from auto-showing
        dispenseModalElement.addEventListener('show.bs.modal', function(event) {
            // Only allow the modal to show when triggered by a button click
            if (!event.relatedTarget) {
                event.preventDefault();
            }
        });
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>