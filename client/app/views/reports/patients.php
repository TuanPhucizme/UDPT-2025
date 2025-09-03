<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users me-2"></i> Báo cáo bệnh nhân
        </h1>
        <div>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="/reports/sync?type=patients" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Đồng bộ dữ liệu bệnh nhân
                </a>
            <?php endif; ?>
                <a href="/reports" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại tổng quan
                </a>
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
                           value="<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-12 months')) ?>">
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
                    <a href="/reports/patients" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Patient Trend -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Xu hướng bệnh nhân theo tháng</h6>
        </div>
        <div class="card-body">
            <?php if (empty($patientStats['data'])): ?>
                <div class="alert alert-info">
                    Không có dữ liệu bệnh nhân trong khoảng thời gian đã chọn
                </div>
            <?php else: ?>
                <div class="chart-container mb-4" style="height: 400px;">
                    <canvas id="patientTrendChart"></canvas>
                </div>
                
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-hover" id="patientsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tháng</th>
                                <th>Số lượng bệnh nhân</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patientStats['data'] as $stat): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            $date = DateTime::createFromFormat('Y-m', $stat['month_year']);
                                            echo $date ? $date->format('m/Y') : $stat['month_year'];
                                        ?>
                                    </td>
                                    <td><?= number_format($stat['patient_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Department Patient Distribution -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Phân bố bệnh nhân theo khoa</h6>
        </div>
        <div class="card-body">
            <div class="chart-container mb-4" style="height: 300px;">
                <canvas id="departmentPatientChart"></canvas>
            </div>
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
    $('#patientsTable').DataTable({
        order: [[0, 'desc']], // Sort by date descending
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json'
        }
    });
    
    // Patient Trend Chart
    const patientTrendLabels = [];
    const patientTrendData = [];
    
    <?php if (!empty($patientStats['data'])): ?>
        <?php foreach (array_reverse($patientStats['data']) as $stat): ?>
            patientTrendLabels.push('<?= $stat['month_year'] ?>');
            patientTrendData.push(<?= $stat['patient_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const patientCtx = document.getElementById('patientTrendChart');
    if (patientCtx) {
        new Chart(patientCtx, {
            type: 'line',
            data: {
                labels: patientTrendLabels,
                datasets: [{
                    label: 'Số lượng bệnh nhân',
                    data: patientTrendData,
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Bệnh nhân: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Department Patient Distribution Chart
    const departmentLabels = [];
    const departmentData = [];
    
    <?php if (!empty($departmentStats['data'])): ?>
        <?php foreach ($departmentStats['data'] as $department): ?>
            departmentLabels.push('<?= addslashes($department['department_name']) ?>');
            departmentData.push(<?= $department['patient_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const deptCtx = document.getElementById('departmentPatientChart');
    if (deptCtx) {
        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: departmentLabels,
                datasets: [{
                    data: departmentData,
                    backgroundColor: [
                        "#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", 
                        "#6f42c1", "#fd7e14", "#20c9a6", "#5a5c69", "#858796"
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>