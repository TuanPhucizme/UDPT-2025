<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-hospital me-2"></i> Báo cáo chuyên khoa
        </h1>
        <div>
            <a href="/reports" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại tổng quan
            </a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="/reports/sync?type=records" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Đồng bộ dữ liệu chuyên khoa
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
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')) ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="/reports/departments" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Department Distribution -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Phân bố bệnh nhân theo chuyên khoa</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="departmentPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php if (!empty($departmentStats['data'])): ?>
                            <?php foreach (array_slice($departmentStats['data'], 0, 5) as $index => $dept): ?>
                                <span class="me-2">
                                    <?php
                                    $colors = ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b"];
                                    $color = $colors[$index % count($colors)];
                                    ?>
                                    <i class="fas fa-circle" style="color: <?= $color ?>;"></i> <?= htmlspecialchars($dept['department_name']) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Số lượng bệnh án theo chuyên khoa</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="departmentBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Statistics Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thống kê chi tiết theo chuyên khoa</h6>
        </div>
        <div class="card-body">
            <?php if (empty($departmentStats['data'])): ?>
                <div class="alert alert-info">Không có dữ liệu chuyên khoa trong khoảng thời gian đã chọn</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="departmentsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Khoa</th>
                                <th>Tên chuyên khoa</th>
                                <th>Số bệnh nhân</th>
                                <th>Số lượt khám</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalRecords = 0;
                            foreach ($departmentStats['data'] as $dept) {
                                $totalRecords += $dept['record_count'];
                            }
                            
                            foreach ($departmentStats['data'] as $dept): 
                                $percentage = $totalRecords > 0 ? ($dept['record_count'] / $totalRecords) * 100 : 0;
                            ?>
                                <tr>
                                    <td><?= $dept['department_id'] ?></td>
                                    <td><?= htmlspecialchars($dept['department_name']) ?></td>
                                    <td><?= number_format($dept['patient_count']) ?></td>
                                    <td><?= number_format($dept['record_count']) ?></td>
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
    $('#departmentsTable').DataTable({
        order: [[2, 'desc']], // Sort by patient count by default
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json'
        }
    });
    
    // Department Pie Chart
    const deptLabels = [];
    const deptPatientData = [];
    const backgroundColors = ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", "#6f42c1", "#fd7e14", "#20c9a6", "#5a5c69", "#858796"];
    
    <?php if (!empty($departmentStats['data'])): ?>
        <?php foreach ($departmentStats['data'] as $index => $dept): ?>
            deptLabels.push('<?= addslashes($dept['department_name']) ?>');
            deptPatientData.push(<?= $dept['patient_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const pieCtx = document.getElementById('departmentPieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: deptLabels,
                datasets: [{
                    data: deptPatientData,
                    backgroundColor: backgroundColors,
                    hoverBackgroundColor: backgroundColors.map(color => color.replace(')', ', 0.8)')),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw} bệnh nhân`;
                            }
                        }
                    }
                },
            },
        });
    }
    
    // Department Bar Chart
    const recordData = [];
    
    <?php if (!empty($departmentStats['data'])): ?>
        <?php foreach ($departmentStats['data'] as $dept): ?>
            recordData.push(<?= $dept['record_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const barCtx = document.getElementById('departmentBarChart');
    if (barCtx) {
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Số lượt khám',
                    data: recordData,
                    backgroundColor: '#4e73df',
                    borderColor: '#4e73df',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
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