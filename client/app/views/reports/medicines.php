<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-pills me-2"></i> Báo cáo thuốc
        </h1>
        <div>
            <a href="/reports" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Quay lại tổng quan
            </a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="/reports/sync?type=prescriptions" class="btn btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i> Đồng bộ dữ liệu thuốc
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
                    <label for="is_liquid" class="form-label">Loại thuốc</label>
                    <select class="form-select" id="is_liquid" name="is_liquid">
                        <option selected disabled>Chọn loại thuốc</option>
                        <option value="true" <?= isset($_GET['is_liquid']) && $_GET['is_liquid'] === 'true' ? 'selected' : '' ?>>
                            Thuốc dạng lỏng
                        </option>
                        <option value="false" <?= isset($_GET['is_liquid']) && $_GET['is_liquid'] === 'false' ? 'selected' : '' ?>>
                            Thuốc dạng rắn
                        </option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                    <a href="/reports/medicines" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Medicine Report -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Thống kê sử dụng thuốc</h6>
            <button class="btn btn-sm btn-outline-primary" id="toggleChartBtn">
                <i class="fas fa-chart-bar me-1"></i> Hiển thị biểu đồ
            </button>
        </div>
        <div class="card-body">
            <div class="chart-container mb-4" style="height: 400px; display: none;">
                <canvas id="medicineUsageChart"></canvas>
            </div>

            <?php if (empty($medicineStats['data'])): ?>
                <div class="alert alert-info">
                    Không có dữ liệu thuốc trong khoảng thời gian đã chọn
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="medicinesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tên thuốc</th>
                                <th>Loại</th>
                                <th>Số lần kê toa</th>
                                <th>Tổng số lượng</th>
                                <th>Thể tích (nếu dạng lỏng)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicineStats['data'] as $medicine): ?>
                                <tr>
                                    <td><?= htmlspecialchars($medicine['medicine_name']) ?></td>
                                    <td>
                                        <?php if ($medicine['is_liquid']): ?>
                                            <span class="badge bg-info">Dạng lỏng</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Dạng rắn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($medicine['prescription_count']) ?></td>
                                    <td><?= number_format($medicine['total_quantity']) ?></td>
                                    <td>
                                        <?php if ($medicine['is_liquid'] && $medicine['total_liquid_volume'] > 0): ?>
                                            <?= number_format($medicine['total_liquid_volume'], 1) ?> ml
                                        <?php else: ?>
                                            -
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

    <!-- Liquid Medicines Report -->
    <?php
    $liquidMedicines = array_filter($medicineStats['data'] ?? [], function($med) {
        return $med['is_liquid'] ?? false;
    });
    
    if (!empty($liquidMedicines)):
    ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thống kê thuốc dạng lỏng</h6>
        </div>
        <div class="card-body">
            <div class="chart-container mb-4" style="height: 300px;">
                <canvas id="liquidMedicineChart"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Include Chart.js and DataTables -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#medicinesTable').DataTable({
        order: [[2, 'desc']], // Sort by prescription count by default
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json'
        }
    });
    
    // Toggle chart visibility
    document.getElementById('toggleChartBtn').addEventListener('click', function() {
        const chartContainer = document.querySelector('.chart-container');
        if (chartContainer.style.display === 'none') {
            chartContainer.style.display = 'block';
            this.innerHTML = '<i class="fas fa-table me-1"></i> Hiển thị bảng';
        } else {
            chartContainer.style.display = 'none';
            this.innerHTML = '<i class="fas fa-chart-bar me-1"></i> Hiển thị biểu đồ';
        }
    });
    
    // Medicine Usage Chart
    const medicineData = {
        labels: [],
        datasets: [{
            label: 'Số lần kê toa',
            data: [],
            backgroundColor: 'rgba(78, 115, 223, 0.8)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 1
        }]
    };
    
    <?php if (!empty($medicineStats['data'])): ?>
        <?php
        $topMedicines = array_slice($medicineStats['data'], 0, 10);
        foreach ($topMedicines as $medicine):
        ?>
            medicineData.labels.push('<?= addslashes($medicine['medicine_name']) ?>');
            medicineData.datasets[0].data.push(<?= $medicine['prescription_count'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>
    
    const medCtx = document.getElementById('medicineUsageChart');
    if (medCtx) {
        new Chart(medCtx, {
            type: 'bar',
            data: medicineData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Số lần kê toa'
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
    }
    
    // Liquid Medicine Chart
    <?php if (!empty($liquidMedicines)): ?>
        const liquidData = {
            labels: [],
            datasets: [{
                label: 'Thể tích sử dụng (ml)',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };
        
        <?php
        $topLiquidMedicines = array_slice($liquidMedicines, 0, 10);
        foreach ($topLiquidMedicines as $medicine):
        ?>
            liquidData.labels.push('<?= addslashes($medicine['medicine_name']) ?>');
            liquidData.datasets[0].data.push(<?= $medicine['total_liquid_volume'] ?? 0 ?>);
        <?php endforeach; ?>
        
        const liquidCtx = document.getElementById('liquidMedicineChart');
        if (liquidCtx) {
            new Chart(liquidCtx, {
                type: 'bar',
                data: liquidData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Thể tích (ml)'
                            }
                        }
                    }
                }
            });
        }
    <?php endif; ?>
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>