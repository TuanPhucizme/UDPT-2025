<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Chỉnh sửa thuốc</h5>
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
                    
                    <form method="POST" action="/medicines/edit/<?= $medicine['id'] ?>">
                        <div class="mb-3">
                            <label for="ten_thuoc" class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ten_thuoc" name="ten_thuoc" 
                                   value="<?= htmlspecialchars($medicine['ten_thuoc']) ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="don_vi" class="form-label">Đơn vị <span class="text-danger">*</span></label>
                                    <select class="form-select" id="don_vi" name="don_vi" 
                                            <?= $medicine['is_liquid'] ? 'disabled' : '' ?> required>
                                        <option value="">Chọn đơn vị</option>
                                        <option value="viên" <?= $medicine['don_vi'] === 'viên' ? 'selected' : '' ?>>Viên</option>
                                        <option value="gói" <?= $medicine['don_vi'] === 'gói' ? 'selected' : '' ?>>Gói</option>
                                        <option value="ống" <?= $medicine['don_vi'] === 'ống' ? 'selected' : '' ?>>Ống</option>
                                        <option value="chai" <?= $medicine['don_vi'] === 'chai' ? 'selected' : '' ?>>Chai</option>
                                        <option value="vỉ" <?= $medicine['don_vi'] === 'vỉ' ? 'selected' : '' ?>>Vỉ</option>
                                        <option value="hộp" <?= $medicine['don_vi'] === 'hộp' ? 'selected' : '' ?>>Hộp</option>
                                        <option value="ml" <?= $medicine['don_vi'] === 'ml' ? 'selected' : '' ?>>ml</option>
                                        <option value="g" <?= $medicine['don_vi'] === 'g' ? 'selected' : '' ?>>g</option>
                                    </select>
                                    <?php if ($medicine['is_liquid']): ?>
                                        <input type="hidden" name="don_vi" value="chai">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="don_gia" class="form-label">Đơn giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="don_gia" name="don_gia" 
                                           value="<?= $medicine['don_gia'] ?>" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isLiquid" name="is_liquid" value="1" 
                                       <?= $medicine['is_liquid'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isLiquid">
                                    Thuốc dạng lỏng (đựng trong chai)
                                </label>
                            </div>
                        </div>
                        
                        <div class="row liquid-fields" <?= !$medicine['is_liquid'] ? 'style="display: none;"' : '' ?>>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="volumePerBottle" class="form-label">Thể tích mỗi chai</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="volumePerBottle" 
                                              name="volume_per_bottle" value="<?= $medicine['volume_per_bottle'] ?? 100 ?>">
                                        <select class="form-select" name="volume_unit" style="max-width: 100px;">
                                            <option value="ml" <?= ($medicine['volume_unit'] ?? 'ml') === 'ml' ? 'selected' : '' ?>>ml</option>
                                            <option value="L" <?= ($medicine['volume_unit'] ?? '') === 'L' ? 'selected' : '' ?>>L</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng hiện tại</label>
                                    <div class="form-control bg-light">
                                        <?= $medicine['so_luong'] ?> chai 
                                        (<?= ($medicine['so_luong'] * ($medicine['volume_per_bottle'] ?? 0)) ?> 
                                        <?= $medicine['volume_unit'] ?? 'ml' ?>)
                                    </div>
                                    <small class="form-text text-muted">
                                        Để thay đổi số lượng, hãy sử dụng tính năng "Cập nhật kho"
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
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
    const isLiquidCheckbox = document.getElementById('isLiquid');
    const liquidFields = document.querySelector('.liquid-fields');
    const donViSelect = document.getElementById('don_vi');
    
    // Toggle liquid fields visibility
    isLiquidCheckbox.addEventListener('change', function() {
        liquidFields.style.display = this.checked ? 'flex' : 'none';
        
        // If liquid is checked, set don_vi to "chai"
        if (this.checked) {
            donViSelect.value = 'chai';
            donViSelect.disabled = true;
        } else {
            donViSelect.disabled = false;
        }
    });
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>