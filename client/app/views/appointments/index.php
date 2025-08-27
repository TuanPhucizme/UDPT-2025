<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh Sách Lịch Hẹn</h2>
        <div>
            <?php if (in_array($_SESSION['user']['role'], ['letan', 'admin'])): ?>
                <a href="/appointments/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo Lịch Hẹn
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Search Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="keyword" 
                           placeholder="Tên bệnh nhân..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date"
                           value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bệnh Nhân</th>
                    <th>Bác Sĩ</th>
                    <th>Khoa</th>
                    <th>Thời Gian</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có lịch hẹn nào</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><?= htmlspecialchars($apt['id']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($apt['patient_name']) ?></strong><br>
                                <small class="text-muted">SĐT: <?= htmlspecialchars($apt['patient_phone']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($apt['department_name']) ?></td>
                            <td><?= (new DateTime($apt['thoi_gian_hen']))->format('d/m/Y H:i') ?></td>
                            <td>
                                <span class="badge bg-<?= getBadgeColor($apt['status']) ?>">
                                    <?= getStatusText($apt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/appointments/view/<?= $apt['id'] ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function getBadgeColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending': return 'Chờ duyệt';
        case 'confirmed': return 'Đã xác nhận';
        case 'cancelled': return 'Đã hủy';
        default: return 'Không xác định';
    }
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form');
    const tableBody = document.querySelector('tbody');
    
    searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const params = new URLSearchParams();
        
        // Handle keyword param name based on content
        const keyword = formData.get('keyword');
        if (keyword) {
            const paramName = /^\d+$/.test(keyword) ? 'phone' : 'name';
            params.append(paramName, keyword);
        }
        
        // Add other params
        const date = formData.get('date');
        if (date) params.append('date', date);
        
        const status = formData.get('status');
        if (status) params.append('status', status);
        try {
            // Update URL with search params
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({}, '', newUrl);
            
            // Show loading state
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span class="ms-2">Đang tìm kiếm...</span>
                    </td>
                </tr>
            `;
            const response = await fetch(`/api/appointments?${params}`);
            if (!response.ok) throw new Error('Search failed');
            
            const appointments = await response.json();
            
            if (appointments.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center">
                            <i class="fas fa-info-circle"></i> Không tìm thấy kết quả
                        </td>
                    </tr>
                `;
                return;
            }
            
            // Render results
            tableBody.innerHTML = appointments.map(apt => `
                <tr>
                    <td>${apt.id}</td>
                    <td>
                        <strong>${escapeHtml(apt.patient_name)}</strong><br>
                        <small class="text-muted">SĐT: ${escapeHtml(apt.patient_phone || 'N/A')}</small>
                    </td>
                    <td>${escapeHtml(apt.doctor_name)}</td>
                    <td>${escapeHtml(apt.department_name)}</td>
                    <td>${new Date(apt.thoi_gian_hen)}</td>
                    <td>
                        <span class="badge bg-${getBadgeColor(apt.status)}">
                            ${getStatusText(apt.status)}
                        </span>
                    </td>
                    <td>
                        <a href="/appointments/view/${apt.id}" 
                           class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        ${apt.status === 'pending' && userRole === 'bacsi' ? `
                            <a href="/appointments/pending" 
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-clock"></i>
                            </a>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
            
        } catch (error) {
            console.error('Search error:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        Lỗi tìm kiếm: ${error.message}
                    </td>
                </tr>
            `;
        }
    });
    
    // Helper function for HTML escaping
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
</script>