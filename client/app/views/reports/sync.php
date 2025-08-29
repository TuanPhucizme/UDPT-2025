<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-sync-alt me-2"></i> Đồng bộ dữ liệu báo cáo
        </h1>
        <a href="/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại báo cáo
        </a>
    </div>

    <?php if ($_SESSION['user']['role'] !== 'admin'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Bạn không có quyền truy cập chức năng này!
        </div>
    <?php else: ?>
        <!-- Sync Card -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Đồng bộ dữ liệu</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-4">
                            Chọn loại dữ liệu bạn muốn đồng bộ từ các service khác vào hệ thống báo cáo.
                            Quá trình này có thể mất một vài phút tùy thuộc vào lượng dữ liệu.
                        </p>
                        
                        <div class="list-group mb-4">
                            <a href="/reports/sync?type=patients" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><i class="fas fa-users me-2"></i> Đồng bộ dữ liệu bệnh nhân</h5>
                                    <small><i class="fas fa-chevron-right"></i></small>
                                </div>
                                <p class="mb-1">Đồng bộ thông tin bệnh nhân từ Patient Service</p>
                                <small class="text-muted">Bao gồm thông tin cá nhân và hồ sơ bệnh án</small>
                            </a>
                            
                            <a href="/reports/sync?type=prescriptions" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><i class="fas fa-prescription me-2"></i> Đồng bộ đơn thuốc</h5>
                                    <small><i class="fas fa-chevron-right"></i></small>
                                </div>
                                <p class="mb-1">Đồng bộ thông tin đơn thuốc từ Prescription Service</p>
                                <small class="text-muted">Bao gồm đơn thuốc, chi tiết thuốc và trạng thái</small>
                            </a>
                            
                            <a href="/reports/sync?type=records" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><i class="fas fa-clipboard-list me-2"></i> Đồng bộ hồ sơ bệnh án</h5>
                                    <small><i class="fas fa-chevron-right"></i></small>
                                </div>
                                <p class="mb-1">Đồng bộ thông tin hồ sơ bệnh án và chẩn đoán</p>
                                <small class="text-muted">Bao gồm thông tin chẩn đoán và khoa điều trị</small>
                            </a>
                            
                            <a href="/reports/sync" class="list-group-item list-group-item-action list-group-item-primary">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><i class="fas fa-sync-alt me-2"></i> Đồng bộ tất cả dữ liệu</h5>
                                    <small><i class="fas fa-chevron-right"></i></small>
                                </div>
                                <p class="mb-1">Đồng bộ tất cả loại dữ liệu từ tất cả các service</p>
                                <small class="text-muted">Quá trình này có thể mất nhiều thời gian hơn</small>
                            </a>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            Đồng bộ dữ liệu sẽ chỉ lấy những dữ liệu mới hoặc được cập nhật từ các service. 
                            Dữ liệu báo cáo hiện có sẽ không bị xóa.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin đồng bộ gần nhất</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Loại dữ liệu</th>
                                        <th>Lần cuối đồng bộ</th>
                                        <th>Số bản ghi</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($syncStatus['sync_status'])): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Chưa có thông tin đồng bộ</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($syncStatus['sync_status'] as $status): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                        switch ($status['sync_type']) {
                                                            case 'patients': 
                                                                echo '<i class="fas fa-users me-1"></i> Bệnh nhân';
                                                                break;
                                                            case 'prescriptions': 
                                                                echo '<i class="fas fa-prescription me-1"></i> Đơn thuốc';
                                                                break;
                                                            case 'medical_records': 
                                                                echo '<i class="fas fa-clipboard-list me-1"></i> Hồ sơ bệnh án';
                                                                break;
                                                            case 'all': 
                                                                echo '<i class="fas fa-sync-alt me-1"></i> Tất cả dữ liệu';
                                                                break;
                                                            default: 
                                                                echo '<i class="fas fa-database me-1"></i> ' . $status['sync_type'];
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?= $status['last_end_time'] ? date('d/m/Y H:i:s', strtotime($status['last_end_time'])) : 'Đang chạy...' ?>
                                                </td>
                                                <td>
                                                    <?= number_format($status['total_records']) ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        switch ($status['last_status']) {
                                                            case 'completed':
                                                                echo '<span class="badge bg-success">Thành công</span>';
                                                                break;
                                                            case 'running':
                                                                echo '<span class="badge bg-info">Đang chạy</span>';
                                                                break;
                                                            case 'failed':
                                                                echo '<span class="badge bg-danger" title="' . htmlspecialchars($status['last_message']) . '">Thất bại</span>';
                                                                break;
                                                            case 'partially_completed':
                                                                echo '<span class="badge bg-warning" title="' . htmlspecialchars($status['last_message']) . '">Hoàn thành một phần</span>';
                                                                break;
                                                            default:
                                                                echo '<span class="badge bg-secondary">' . $status['last_status'] . '</span>';
                                                        }
                                                    ?>
                                                    <?php if ($status['last_message']): ?>
                                                        <button class="btn btn-sm btn-link p-0 ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($status['last_message']) ?>">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Lưu ý:</strong> Việc đồng bộ dữ liệu có thể tạo tải trọng lớn lên các service. 
                            Nên thực hiện đồng bộ vào thời điểm ít người dùng truy cập hệ thống.
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="/reports/sync?schedule=daily" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-1"></i> Lập lịch đồng bộ hàng ngày
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>