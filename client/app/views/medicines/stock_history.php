<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i> Lịch sử kho thuốc: <?= htmlspecialchars($medicine['ten_thuoc']) ?>
                    </h5>
                    <a href="/medicines" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Medicine Info -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Thông tin thuốc</h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="fw-bold">Tên thuốc:</div>
                                                <div><?= htmlspecialchars($medicine['ten_thuoc']) ?></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="fw-bold">Mã thuốc:</div>
                                                <div>#<?= $medicine['id'] ?></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="fw-bold">Đơn vị:</div>
                                                <div><?= htmlspecialchars($medicine['don_vi']) ?></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="fw-bold">Đơn giá:</div>
                                                <div><?= number_format($medicine['don_gia']) ?> VNĐ</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card <?= $medicine['so_luong'] > 10 ? 'bg-success text-white' : ($medicine['so_luong'] > 0 ? 'bg-warning' : 'bg-danger text-white') ?>">
                                    <div class="card-body">
                                        <h6 class="card-title">Tình trạng kho hiện tại</h6>
                                        <div class="d-flex align-items-center">
                                            <div class="display-4 me-3"><?= $medicine['so_luong'] ?></div>
                                            <div>
                                                <div class="fs-5"><?= $medicine['is_liquid'] ? 'chai' : $medicine['don_vi'] ?></div>
                                                <?php if ($medicine['is_liquid']): ?>
                                                    <div class="mt-1">
                                                        Tổng thể tích: <?= $medicine['so_luong'] * $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($medicine['so_luong'] <= 10): ?>
                                            <div class="mt-2">
                                                <?php if ($medicine['so_luong'] <= 0): ?>
                                                    <div class="alert alert-danger mb-0 py-1">
                                                        <i class="fas fa-exclamation-triangle"></i> Hết hàng
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-warning mb-0 py-1">
                                                        <i class="fas fa-exclamation-triangle"></i> Sắp hết hàng
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stock History -->
                    <h6 class="text-muted mb-3">Lịch sử kho</h6>
                    <?php if (empty($stockHistory)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Chưa có lịch sử thay đổi kho cho thuốc này
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Loại thao tác</th>
                                        <th>Số lượng</th>
                                        <th>Người thực hiện</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stockHistory as $history): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></td>
                                            <td>
                                                <?php if ($history['action_type'] === 'purchase'): ?>
                                                    <span class="badge bg-success">Nhập kho</span>
                                                <?php elseif ($history['action_type'] === 'dispense'): ?>
                                                    <span class="badge bg-primary">Phát thuốc</span>
                                                <?php elseif ($history['action_type'] === 'adjustment'): ?>
                                                    <span class="badge bg-warning text-dark">Điều chỉnh</span>
                                                <?php elseif ($history['action_type'] === 'return'): ?>
                                                    <span class="badge bg-secondary">Trả hàng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($history['quantity_change'] > 0): ?>
                                                    <span class="text-success">+<?= $history['quantity_change'] ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger"><?= $history['quantity_change'] ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if ($medicine['is_liquid'] && isset($history['volume_used'])): ?>
                                                    <div class="small text-muted">
                                                        <?= abs($history['volume_used']) ?> <?= $medicine['volume_unit'] ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($history['created_by_name'] ?? 'Hệ thống') ?></td>
                                            <td><?= htmlspecialchars($history['note'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user']['role'] === 'duocsi' || $_SESSION['user']['role'] === 'admin'): ?>
                        <div class="mt-3 text-center">
                            <a href="/medicines/update-stock/<?= $medicine['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Thêm nhập/xuất kho mới
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>