<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2"></i> Cập nhật kho thuốc</h5>
                    <a href="/medicines" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Thông tin thuốc</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Tên thuốc:</strong> <?= htmlspecialchars($medicine['ten_thuoc']) ?></p>
                                        <p class="mb-1"><strong>Đơn vị:</strong> <?= htmlspecialchars($medicine['don_vi']) ?></p>
                                        <p class="mb-1"><strong>Đơn giá:</strong> <?= number_format($medicine['don_gia']) ?> VNĐ</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <strong>Số lượng hiện tại:</strong> 
                                            <span class="badge bg-<?= $medicine['so_luong'] > 0 ? 'success' : 'danger' ?>">
                                                <?= $medicine['so_luong'] ?> <?= $medicine['is_liquid'] ? 'chai' : $medicine['don_vi'] ?>
                                            </span>
                                        </p>
                                        <?php if ($medicine['is_liquid']): ?>
                                            <p class="mb-1"><strong>Loại:</strong> Thuốc dạng lỏng</p>
                                            <p class="mb-1">
                                                <strong>Thể tích mỗi chai:</strong> 
                                                <?= $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?>
                                            </p>
                                            <p class="mb-1">
                                                <strong>Tổng thể tích:</strong> 
                                                <?= $medicine['so_luong'] * $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="/medicines/updateStock/<?= $medicine['id'] ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="action_type" class="form-label">Loại thao tác <span class="text-danger">*</span></label>
                                    <select class="form-select" id="action_type" name="action_type" required>
                                        <option value="">Chọn thao tác</option>
                                        <option value="purchase">Nhập kho</option>
                                        <option value="adjustment">Điều chỉnh số lượng</option>
                                        <option value="return">Trả hàng</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Số lượng <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                        <span class="input-group-text">
                                            <?= $medicine['is_liquid'] ? 'chai' : $medicine['don_vi'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($medicine['is_liquid']): ?>
                        <div class="mb-3" id="volumeCalculation" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div class="me-2">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading mb-1">Thông tin thể tích</h6>
                                        <p class="mb-0">
                                            Tổng thể tích: <span id="totalVolume">0</span> <?= $medicine['volume_unit'] ?>
                                            <br>
                                            <small class="text-muted">
                                                (<?= $medicine['volume_per_bottle'] ?> <?= $medicine['volume_unit'] ?> × <span id="bottleCount">0</span> chai)
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="note" name="note" rows="3" 
                                      placeholder="Nhập ghi chú cho việc thay đổi kho hàng (không bắt buộc)"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng sau thay đổi</label>
                                    <div class="form-control bg-light">
                                        <span id="finalStock"><?= $medicine['so_luong'] ?></span> <?= $medicine['is_liquid'] ? 'chai' : $medicine['don_vi'] ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($medicine['is_liquid']): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tổng thể tích sau thay đổi</label>
                                    <div class="form-control bg-light">
                                        <span id="finalVolume"><?= $medicine['so_luong'] * $medicine['volume_per_bottle'] ?></span> <?= $medicine['volume_unit'] ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Cập nhật kho
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionTypeSelect = document.getElementById('action_type');
    const quantityInput = document.getElementById('quantity');
    const finalStockSpan = document.getElementById('finalStock');
    const currentStock = <?= $medicine['so_luong'] ?>;
    
    <?php if ($medicine['is_liquid']): ?>
    const volumeCalc = document.getElementById('volumeCalculation');
    const totalVolumeSpan = document.getElementById('totalVolume');
    const bottleCountSpan = document.getElementById('bottleCount');
    const finalVolumeSpan = document.getElementById('finalVolume');
    const volumePerBottle = <?= $medicine['volume_per_bottle'] ?>;
    <?php endif; ?>
    
    // Show/hide volume calculation for liquid medicines
    actionTypeSelect.addEventListener('change', function() {
        <?php if ($medicine['is_liquid']): ?>
        volumeCalc.style.display = this.value ? 'block' : 'none';
        <?php endif; ?>
        updateFinalStock();
    });
    
    // Update calculations when quantity changes
    quantityInput.addEventListener('input', updateFinalStock);
    
    function updateFinalStock() {
        const actionType = actionTypeSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;
        let finalStock = currentStock;
        
        if (actionType === 'purchase') {
            finalStock = currentStock + quantity;
        } else if (actionType === 'adjustment') {
            finalStock = quantity;
        } else if (actionType === 'return') {
            finalStock = Math.max(0, currentStock - quantity);
        }
        
        finalStockSpan.textContent = finalStock;
        
        <?php if ($medicine['is_liquid']): ?>
        // Update volume calculations for liquid medicines
        bottleCountSpan.textContent = quantity;
        totalVolumeSpan.textContent = (quantity * volumePerBottle).toFixed(2);
        finalVolumeSpan.textContent = (finalStock * volumePerBottle).toFixed(2);
        <?php endif; ?>
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>