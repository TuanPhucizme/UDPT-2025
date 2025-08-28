<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-pills me-2"></i> Quản lý thuốc</h2>
            <p class="text-muted">Quản lý danh sách thuốc và kho thuốc</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="/medicines" class="btn btn-primary active">
                    <i class="fas fa-list me-1"></i> Danh sách thuốc
                </a>
                <a href="/medicines/report" class="btn btn-outline-secondary">
                    <i class="fas fa-chart-bar me-1"></i> Báo cáo thuốc
                </a>
                <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/medicines/create" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Thêm thuốc mới
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Bộ lọc</h5>
        </div>
        <div class="card-body">
            <!-- Replace the filter form in index.php -->
<form action="/medicines" method="GET" class="row g-3">
    <div class="col-md-5">
        <label for="search" class="form-label">Tìm kiếm</label>
        <input type="text" class="form-control" id="search" name="search" 
            placeholder="Tên thuốc..."
            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
    </div>
    <div class="col-md-4">
        <label for="filter" class="form-label">Tình trạng kho</label>
        <select class="form-select" id="filter" name="filter">
            <option value="all">Tất cả</option>
            <option value="low" <?= isset($_GET['filter']) && $_GET['filter'] === 'low' ? 'selected' : '' ?>>
                Sắp hết
            </option>
            <option value="out" <?= isset($_GET['filter']) && $_GET['filter'] === 'out' ? 'selected' : '' ?>>
                Hết hàng
            </option>
            <option value="liquid" <?= isset($_GET['filter']) && $_GET['filter'] === 'liquid' ? 'selected' : '' ?>>
                Thuốc dạng lỏng
            </option>
        </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-filter me-1"></i> Lọc
        </button>
        <a href="/medicines" class="btn btn-outline-secondary">
            <i class="fas fa-redo me-1"></i> Đặt lại
        </a>
    </div>
</form>
        </div>
    </div>

    <!-- Medicine List -->
    <div class="card shadow">
        <div class="card-body">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= !isset($_GET['filter']) || $_GET['filter'] === 'all' ? 'active' : '' ?>" href="/medicines?filter=all">
                        Tất cả thuốc
                        <span class="badge bg-secondary ms-1"><?= count($medicines) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] === 'low' ? 'active' : '' ?>" href="/medicines?filter=low">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Sắp hết hàng
                        <?php if ($lowStockCount > 0): ?>
                            <span class="badge bg-warning text-dark"><?= $lowStockCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] === 'out' ? 'active' : '' ?>" href="/medicines?filter=out">
                        <i class="fas fa-ban text-danger"></i> Hết hàng
                        <?php if ($outOfStockCount > 0): ?>
                            <span class="badge bg-danger"><?= $outOfStockCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isset($_GET['filter']) && $_GET['filter'] === 'liquid' ? 'active' : '' ?>" href="/medicines?filter=liquid">
                        <i class="fas fa-tint text-info"></i> Dạng lỏng
                        <?php if ($liquidMedicinesCount > 0): ?>
                            <span class="badge bg-info"><?= $liquidMedicinesCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <?php if (empty($medicines)): ?>
                <div class="text-center py-5">
                    <img src="/assets/images/empty-list.svg" alt="Không có thuốc" style="width: 120px;" class="mb-3">
                    <h5>Không tìm thấy thuốc nào</h5>
                    <p class="text-muted">Hãy thử thay đổi bộ lọc hoặc thêm thuốc mới</p>
                    
                    <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                        <a href="/medicines/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Thêm thuốc mới
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mã</th>
                                <th>Tên thuốc</th>
                                <th>Đơn vị</th>
                                <th>Loại</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicines as $medicine): ?>
                                <tr>
                                    <td><?= $medicine['id'] ?></td>
                                    <td><?= htmlspecialchars($medicine['ten_thuoc']) ?></td>
                                    <td><?= htmlspecialchars($medicine['don_vi']) ?></td>
                                    <td>
                                        <?php if ($medicine['is_liquid']): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-tint me-1"></i> Dạng lỏng
                                            </span>
                                            <div class="small text-muted mt-1">
                                                <?= $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?>/chai
                                            </div>
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
                                                Sắp hết (<?= $medicine['so_luong'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <?= $medicine['so_luong'] ?> <?= $medicine['is_liquid'] ? 'chai' : $medicine['don_vi'] ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($medicine['is_liquid']): ?>
                                            <div class="small text-muted mt-1">
                                                Tổng: <?= $medicine['so_luong'] * $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($medicine['don_gia']) ?> VNĐ</td>
                                    <td>
                                        <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                                            <div class="btn-group">
                                                <a href="/medicines/update-stock/<?= $medicine['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-boxes me-1"></i> Cập nhật kho
                                                </a>
                                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="/medicines/edit/<?= $medicine['id'] ?>">
                                                            <i class="fas fa-edit me-1"></i> Chỉnh sửa thông tin
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="/medicines/stock-history/<?= $medicine['id'] ?>">
                                                            <i class="fas fa-history me-1"></i> Lịch sử kho
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php else: ?>
                                            <a href="/medicines/stock-history/<?= $medicine['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-history me-1"></i> Lịch sử kho
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

<?php require_once '../app/views/layouts/footer.php'; ?>