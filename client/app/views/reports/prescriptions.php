<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-prescription me-2"></i> Báo cáo đơn thuốc
        </h1>
        <div>
            <a href="/reports" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại tổng quan
            </a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="/reports/sync?type=prescriptions" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Đồng bộ dữ liệu đơn thuốc
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lọc báo cáo</h6>
        </div>
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label for="group_by" class="form-label">Nhóm theo</label>
                    <select class="form-select" id="group_by" name="group_by">
                        <option value="day" <?= isset($_GET['group_by']) && $_GET['group_by'] === 'day' ? 'selected' : '' ?>>Ngày</option>
                        <option value="month" <?= isset($_GET['group_by']) && $_GET['group_by'] === 'month' ? 'selected' : '' ?>>Tháng</option>
                        <option value="year" <?= isset($_GET['group_by']) && $_GET['group_by'] === 'year' ? 'selected' : '' ?>>Năm</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="/reports/prescriptions" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescription Summary -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng số đơn thuốc</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $totalPrescriptions = 0;
                                    if (!empty($prescriptionStats['data'])) {
                                        foreach ($prescriptionStats['data'] as $stat) {
                                            $totalPrescriptions += $stat['total_prescriptions'];
                                        }
                                    }
                                    echo number_format($totalPrescriptions);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-prescription fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã phát thuốc</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $dispensedCount = 0;
                                    if (!empty($prescriptionStats['data'])) {
                                        foreach ($prescriptionStats['data'] as $stat) {
                                            $dispensedCount += $stat['dispensed_count'];
                                        }
                                    }
                                    echo number_format($dispensedCount);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Đang chờ phát</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $pendingCount = 0;
                                    if (!empty($prescriptionStats['data'])) {
                                        foreach ($prescriptionStats['data'] as $stat) {
                                            $pendingCount += $stat['pending_count'];
                                        }
                                    }
                                    echo number_format($pendingCount);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Đã hủy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $cancelledCount = 0;
                                    if (!empty($prescriptionStats['data'])) {
                                        foreach ($prescriptionStats['data'] as $stat) {
                                            $cancelledCount += $stat['cancelled_count'];
                                        }
                                    }
                                    echo number_format($cancelledCount);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prescription Trend Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Xu hướng đơn thuốc theo thời gian</h6>
        </div>
        <div class="card-body">
            <?php if (empty($prescriptionStats['data'])): ?>
                <div class="alert alert-info">
                    Không có dữ liệu đơn thuốc trong khoảng thời gian đã chọn
                </div>
            <?php else: ?>
                <div class="chart-container mb-4" style="height: 400px;">
                    <canvas id="prescriptionTrendChart"></canvas>
                </div>
                
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover" id="prescriptionsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Tổng đơn thuốc</th>
                                <th>Đã phát thuốc</th>
                                <th>Đang chờ phát</th>
                                <th>Đã hủy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptionStats['data'] as $stat): ?>
                                <tr>
                                    <td><?= $stat['report_date'] ?></td>
                                    <td><?= number_format($stat['total_prescriptions']) ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= number_format($stat['dispensed_count']) ?>
                                            (<?= $stat['total_prescriptions'] > 0 ? round(($stat['dispensed_count'] / $stat['total_prescriptions']) * 100) : 0 ?>%)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <?= number_format($stat['pending_count']) ?>
                                            (<?= $stat['total_prescriptions'] > 0 ? round(($stat['pending_count'] / $stat['total_prescriptions']) * 100) : 0 ?>%)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?= number_format($stat['cancelled_count']) ?>
                                            (<?= $stat['total_prescriptions'] > 0 ? round(($stat['cancelled_count'] / $stat['total_prescriptions']) * 100) : 0 ?>%)
                                        </span>
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

<!-- Include Chart.js and DataTables -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#prescriptionsTable').DataTable({
        order: [[0, 'desc']], // Sort by date descending
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json'
        }
    });
    
    // Prescription Trend Chart
    const prescriptionLabels = [];
    const totalPrescriptions = [];
    const dispensedData = [];
    const pendingData = [];
    const cancelledData = [];
    
    <?php if (!empty($prescriptionStats['data'])): ?>
        <?php foreach (array_reverse($prescriptionStats['data']) as $stat): ?>
            prescriptionLabels.push('<?= $stat['report_date'] ?>');
            totalPrescriptions.push(<?= $stat['total_prescriptions'] ?>);
            dispensedData.push(<?= $stat['dispensed_count'] ?>);
            pendingData.push(<?= $stat['pending_count'] ?>);
            cancelledData.push(<?= $stat['cancelled_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const prescriptionCtx = document.getElementById('prescriptionTrendChart');
    if (prescriptionCtx) {
        new Chart(prescriptionCtx, {
            type: 'bar',
            data: {
                labels: prescriptionLabels,
                datasets: [
                    {
                        label: 'Đã phát thuốc',
                        data: dispensedData,
                        backgroundColor: 'rgba(28, 200, 138, 0.8)',
                        borderColor: 'rgba(28, 200, 138, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Đang chờ phát',
                        data: pendingData,
                        backgroundColor: 'rgba(246, 194, 62, 0.8)',
                        borderColor: 'rgba(246, 194, 62, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Đã hủy',
                        data: cancelledData,
                        backgroundColor: 'rgba(231, 74, 59, 0.8)',
                        borderColor: 'rgba(231, 74, 59, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Số lượng đơn thuốc'
                        },
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>