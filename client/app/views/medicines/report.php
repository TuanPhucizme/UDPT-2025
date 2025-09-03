<?php require_once '../app/views/layouts/header.php'; ?>


<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">
                <i class="fas fa-chart-bar me-2"></i> Báo cáo thuốc
            </h2>
            <p class="text-muted mb-0">Báo cáo sử dụng thuốc và tình trạng kho</p>
        </div>

        <div class="d-flex">
            <div class="d-flex justify-content-end">
                <a href="/medicines/report" class="btn btn-outline-secondary shadow-sm d-inline-flex align-items-center px-3 me-2">
                    <i class="fas fa-chart-bar me-1"></i> Báo cáo thuốc
                </a>
                <a href="/medicines" class="btn btn-primary shadow-sm d-inline-flex align-items-center px-3 me-2 active">
                    <i class="fas fa-list me-1"></i> Danh sách thuốc
                </a>
                <a href="/" class="btn btn-outline-dark shadow-sm d-inline-flex align-items-center px-3">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <!-- Liquid Medicines Report -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0"><i class="fas fa-tint me-2"></i> Báo cáo thuốc dạng lỏng</h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#liquidMedicinesReport">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div id="liquidMedicinesReport" class="collapse show">
            <div class="card-body">
                <?php if (empty($liquidMedicines)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Không có thuốc dạng lỏng nào trong hệ thống
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên thuốc</th>
                                    <th>Thể tích mỗi chai</th>
                                    <th>Số chai trong kho</th>
                                    <th>Tổng thể tích hiện có</th>
                                    <th>Đã sử dụng tháng này</th>
                                    <th>Tỷ lệ sử dụng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($liquidMedicines as $medicine): ?>
                                    <?php
                                        $totalVolume = $medicine['volume_per_bottle'] * $medicine['so_luong'];
                                        $usedVolume = $medicine['volume_used'] ?? 0;
                                        $usageRate = ($medicine['so_luong'] > 0) ? 
                                            ($usedVolume / $totalVolume) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="/medicines/stock-history/<?= $medicine['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($medicine['ten_thuoc']) ?>
                                            </a>
                                        </td>
                                        <td><?= $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?></td>
                                        <td>
                                            <?php if ($medicine['so_luong'] <= 0): ?>
                                                <span class="badge bg-danger">Hết hàng (0)</span>
                                            <?php elseif ($medicine['so_luong'] <= 5): ?>
                                                <span class="badge bg-warning text-dark">Sắp hết (<?= $medicine['so_luong'] ?>)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?= $medicine['so_luong'] ?> chai</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $totalVolume ?> <?= $medicine['volume_unit'] ?></td>
                                        <td><?= $usedVolume ?> <?= $medicine['volume_unit'] ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?= $usageRate > 80 ? 'bg-danger' : ($usageRate > 50 ? 'bg-warning' : 'bg-success') ?>" 
                                                    role="progressbar" 
                                                    style="width: <?= min(100, $usageRate) ?>%" 
                                                    aria-valuenow="<?= $usageRate ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <?= number_format($usageRate, 1) ?>%
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
    
    <!-- Stock Status Report -->
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Thuốc cần nhập thêm</h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#lowStockReport">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div id="lowStockReport" class="collapse show">
            <div class="card-body">
                <?php if (empty($lowStockMedicines)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Tất cả thuốc đều có đủ số lượng trong kho
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên thuốc</th>
                                    <th>Loại</th>
                                    <th>Số lượng hiện tại</th>
                                    <th>Ngày nhập cuối</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockMedicines as $medicine): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($medicine['ten_thuoc']) ?></td>
                                        <td>
                                            <?php if ($medicine['is_liquid']): ?>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-tint me-1"></i> Dạng lỏng
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-pills me-1"></i> Dạng rắn
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($medicine['so_luong'] <= 0): ?>
                                                <span class="badge bg-danger">Hết hàng</span>
                                            <?php elseif ($medicine['so_luong'] <= 10): ?>
                                                <span class="badge bg-warning text-dark">
                                                    Sắp hết (<?= $medicine['so_luong'] ?> <?= $medicine['is_liquid'] ? 'chai' : $medicine['don_vi'] ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($medicine['last_purchase_date'])) ?></td>
                                        <td>
                                            <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                                                <a href="/medicines/update-stock/<?= $medicine['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-plus me-1"></i> Nhập kho
                                                </a>
                                            <?php else: ?>
                                                <a href="/medicines/stock-history/<?= $medicine['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-history me-1"></i> Xem lịch sử
                                                </a>
                                            <?php endif; ?>
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
    
    <!-- Monthly Usage Statistics -->
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Thống kê sử dụng thuốc theo tháng</h5>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#monthlyReport">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        
        <div id="monthlyReport" class="collapse show">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="monthSelector">
                            <?php
                                $currentMonth = date('m');
                                $currentYear = date('Y');
                                for ($i = 0; $i < 12; $i++) {
                                    $month = ($currentMonth - $i) > 0 ? $currentMonth - $i : 12 + ($currentMonth - $i);
                                    $year = ($currentMonth - $i) > 0 ? $currentYear : $currentYear - 1;
                                    $monthName = date('F', mktime(0, 0, 0, $month, 1));
                                    echo "<option value=\"{$month}-{$year}\"" . ($i === 0 ? ' selected' : '') . ">{$monthName} {$year}</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="chart-container" style="position: relative; height:400px; width:100%">
                    <canvas id="monthlyUsageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sample data for the chart - in a real application, this would come from the backend
    const medicineData = {
        '8-2025': {
            labels: ['Paracetamol', 'Amoxicillin', 'Omeprazole', 'Vitamin C', 'Loratadine'],
            data: [120, 85, 65, 45, 30]
        },
        '7-2025': {
            labels: ['Paracetamol', 'Amoxicillin', 'Omeprazole', 'Vitamin C', 'Loratadine'],
            data: [100, 70, 60, 40, 25]
        }
    };
    
    // Initialize chart
    const ctx = document.getElementById('monthlyUsageChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: medicineData['8-2025'].labels,
            datasets: [{
                label: 'Số lượng sử dụng',
                data: medicineData['8-2025'].data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Số lượng'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Thuốc'
                    }
                }
            }
        }
    });
    
    // Update chart when month changes
    document.getElementById('monthSelector').addEventListener('change', function() {
        const selectedMonth = this.value;
        if (medicineData[selectedMonth]) {
            monthlyChart.data.labels = medicineData[selectedMonth].labels;
            monthlyChart.data.datasets[0].data = medicineData[selectedMonth].data;
            monthlyChart.update();
        }
    });
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>