<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-stethoscope me-2"></i> Báo cáo chẩn đoán
        </h1>
        <div>
            <a href="/reports" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại tổng quan
            </a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="/reports/sync?type=records" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Đồng bộ dữ liệu bệnh án
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
                    <label for="limit" class="form-label">Số lượng hiển thị</label>
                    <select class="form-select" id="limit" name="limit">
                        <option value="10" <?= isset($_GET['limit']) && $_GET['limit'] == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= isset($_GET['limit']) && $_GET['limit'] == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= isset($_GET['limit']) && $_GET['limit'] == 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= isset($_GET['limit']) && $_GET['limit'] == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="/reports/diagnoses" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Diagnoses Chart and Table -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Phân bố chẩn đoán phổ biến</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($diagnosisStats['data'])): ?>
                        <div class="alert alert-info">
                            Không có dữ liệu chẩn đoán trong khoảng thời gian đã chọn
                        </div>
                    <?php else: ?>
                        <div class="chart-bar" style="height: 500px;">
                            <canvas id="diagnosisChart"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 chẩn đoán phổ biến nhất</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($diagnosisStats['data'])): ?>
                        <div class="alert alert-info">
                            Không có dữ liệu chẩn đoán trong khoảng thời gian đã chọn
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php 
                            $topDiagnoses = array_slice($diagnosisStats['data'], 0, 10);
                            $maxCount = $topDiagnoses[0]['record_count'];
                            foreach ($topDiagnoses as $index => $diagnosis): 
                                $percentage = ($diagnosis['record_count'] / $maxCount) * 100;
                            ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($diagnosis['diagnosis']) ?></h6>
                                        <span class="badge bg-primary"><?= number_format($diagnosis['record_count']) ?></span>
                                    </div>
                                    <div class="progress mt-2" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" 
                                             aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnoses Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách tất cả chẩn đoán</h6>
        </div>
        <div class="card-body">
            <?php if (empty($diagnosisStats['data'])): ?>
                <div class="alert alert-info">Không có dữ liệu chẩn đoán trong khoảng thời gian đã chọn</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="diagnosesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Chẩn đoán</th>
                                <th>Số lượng bệnh án</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalRecords = 0;
                            foreach ($diagnosisStats['data'] as $diagnosis) {
                                $totalRecords += $diagnosis['record_count'];
                            }
                            
                            foreach ($diagnosisStats['data'] as $index => $diagnosis): 
                                $percentage = $totalRecords > 0 ? ($diagnosis['record_count'] / $totalRecords) * 100 : 0;
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($diagnosis['diagnosis']) ?></td>
                                    <td><?= number_format($diagnosis['record_count']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $percentage ?>%;" 
                                                 aria-valuenow="<?= $percentage ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= number_format($percentage, 1) ?>%
                                            </div>
                                        </div>
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
    $('#diagnosesTable').DataTable({
        order: [[2, 'desc']], // Sort by record count by default
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json'
        }
    });
    
    // Diagnoses Horizontal Bar Chart
    const diagnosisLabels = [];
    const diagnosisData = [];
    const colors = [];
    
    <?php if (!empty($diagnosisStats['data'])): ?>
        <?php
        $chartColors = [
            'rgba(78, 115, 223, 0.8)',
            'rgba(28, 200, 138, 0.8)',
            'rgba(54, 185, 204, 0.8)',
            'rgba(246, 194, 62, 0.8)',
            'rgba(231, 74, 59, 0.8)',
            'rgba(111, 66, 193, 0.8)',
            'rgba(253, 126, 20, 0.8)',
            'rgba(32, 201, 166, 0.8)',
            'rgba(90, 92, 105, 0.8)',
            'rgba(133, 135, 150, 0.8)',
        ];
        
        // Only show top 20 diagnoses in chart for readability
        $chartData = array_slice($diagnosisStats['data'], 0, 20);
        foreach ($chartData as $index => $diagnosis):
            $colorIndex = $index % count($chartColors);
        ?>
            diagnosisLabels.push('<?= addslashes($diagnosis['diagnosis']) ?>');
            diagnosisData.push(<?= $diagnosis['record_count'] ?>);
            colors.push('<?= $chartColors[$colorIndex] ?>');
        <?php endforeach; ?>
    <?php endif; ?>
    
    const diagnosisCtx = document.getElementById('diagnosisChart');
    if (diagnosisCtx) {
        new Chart(diagnosisCtx, {
            type: 'bar',
            data: {
                labels: diagnosisLabels,
                datasets: [{
                    label: 'Số lượng bệnh án',
                    data: diagnosisData,
                    backgroundColor: colors,
                    borderColor: colors.map(color => color.replace('0.8', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.raw} bệnh án`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
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