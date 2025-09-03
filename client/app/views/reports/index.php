<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line me-2"></i> Báo cáo thống kê
        </h1>
        <div>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="/reports/sync" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Đồng bộ dữ liệu
                </a>
            <?php endif; ?>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fas fa-filter me-1"></i> Lọc dữ liệu
            </button>
            <a href="/" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Date Range Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Lọc dữ liệu theo thời gian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/reports" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Từ ngày</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $_GET['start_date'] ?? date('Y-m-d', strtotime('-6 months')) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Đến ngày</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Áp dụng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Patient Statistics Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng bệnh nhân (6 tháng qua)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $totalPatients = 0;
                                    if (!empty($patientStats['data'])) {
                                        foreach ($patientStats['data'] as $stat) {
                                            $totalPatients += $stat['patient_count'];
                                        }
                                    }
                                    echo number_format($totalPatients);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="/reports/patients" class="text-decoration-none text-primary small">
                        Chi tiết <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Prescription Statistics Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đơn thuốc đã phát</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $dispensedCount = 0;
                                    if (!empty($prescriptionStats['data'])) {
                                        foreach ($prescriptionStats['data'] as $stat) {
                                            $dispensedCount += $stat['dispensed_count'] ?? 0;
                                        }
                                    }
                                    echo number_format($dispensedCount);
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-prescription fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="/reports/prescriptions" class="text-decoration-none text-success small">
                        Chi tiết <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Department Statistics Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Khoa hoạt động nhiều nhất</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $topDepartment = !empty($departmentStats['data']) ? 
                                        $departmentStats['data'][0]['department_name'] ?? 'N/A' : 'N/A';
                                    echo $topDepartment;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clinic-medical fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="/reports/departments" class="text-decoration-none text-info small">
                        Chi tiết <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Medicine Statistics Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Thuốc kê nhiều nhất</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                    $topMedicine = !empty($medicineStats['data']) ? 
                                        $medicineStats['data'][0]['medicine_name'] ?? 'N/A' : 'N/A';
                                    echo $topMedicine;
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pills fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="/reports/medicines" class="text-decoration-none text-warning small">
                        Chi tiết <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Patient Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Xu hướng lượt khám bệnh</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="patientTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescription Status Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tỷ lệ trạng thái đơn thuốc</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="prescriptionStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Top Diagnoses Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chẩn đoán phổ biến nhất</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($diagnosisStats['data'])): ?>
                        <div class="alert alert-info mb-0">Không có dữ liệu</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Chẩn đoán</th>
                                        <th>Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($diagnosisStats['data'] as $diagnosis): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($diagnosis['diagnosis']) ?></td>
                                            <td><?= number_format($diagnosis['record_count']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="/reports/diagnoses" class="btn btn-sm btn-primary">
                                Xem tất cả chẩn đoán
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Medicines Table -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thuốc sử dụng nhiều nhất</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($medicineStats['data'])): ?>
                        <div class="alert alert-info mb-0">Không có dữ liệu</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Tên thuốc</th>
                                        <th>Số lần kê</th>
                                        <th>Tổng số lượng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $topMedicines = array_slice($medicineStats['data'], 0, 5);
                                    foreach ($topMedicines as $medicine): 
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($medicine['medicine_name']) ?></td>
                                            <td><?= number_format($medicine['prescription_count']) ?></td>
                                            <td><?= number_format($medicine['total_quantity']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="/reports/medicines" class="btn btn-sm btn-primary">
                                Xem tất cả thuốc
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Patient trend chart
    const patientTrendLabels = [];
    const patientTrendData = [];
    
    <?php if (!empty($patientStats['data'])): ?>
        <?php foreach (array_reverse($patientStats['data']) as $stat): ?>
            patientTrendLabels.push('<?= $stat['month_year'] ?>');
            patientTrendData.push(<?= $stat['patient_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const patientCtx = document.getElementById('patientTrendChart');
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
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Prescription status chart
    let dispensedTotal = 0;
    let pendingTotal = 0;
    let cancelledTotal = 0;
    
    <?php if (!empty($prescriptionStats['data'])): ?>
        <?php foreach ($prescriptionStats['data'] as $stat): ?>
            dispensedTotal += <?= $stat['dispensed_count'] ?? 0 ?>;
            pendingTotal += <?= $stat['pending_count'] ?? 0 ?>;
            cancelledTotal += <?= $stat['cancelled_count'] ?? 0 ?>;
        <?php endforeach; ?>
    <?php endif; ?>
    
    const prescriptionCtx = document.getElementById('prescriptionStatusChart');
    new Chart(prescriptionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Đã phát thuốc', 'Đang chờ', 'Đã hủy'],
            datasets: [{
                data: [dispensedTotal, pendingTotal, cancelledTotal],
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                hoverBackgroundColor: ['#169b6b', '#dda20a', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        },
    });
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>