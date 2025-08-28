<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Thêm thuốc mới</h5>
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
                    
                    <form method="POST" action="/medicines/create">
                        <div class="mb-3">
                            <label for="ten_thuoc" class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ten_thuoc" name="ten_thuoc" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="don_vi" class="form-label">Đơn vị <span class="text-danger">*</span></label>
                                    <select class="form-select" id="don_vi" name="don_vi" required>
                                        <option value="">Chọn đơn vị</option>
                                        <option value="viên">Viên</option>
                                        <option value="gói">Gói</option>
                                        <option value="ống">Ống</option>
                                        <option value="chai">Chai</option>
                                        <option value="vỉ">Vỉ</option>
                                        <option value="hộp">Hộp</option>
                                        <option value="ml">ml</option>
                                        <option value="g">g</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="don_gia" class="form-label">Đơn giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="don_gia" name="don_gia" min="0" step="1000" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="so_luong" class="form-label">Số lượng <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="so_luong" name="so_luong" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isLiquid" name="is_liquid" value="1">
                                <label class="form-check-label" for="isLiquid">
                                    Thuốc dạng lỏng (đựng trong chai)
                                </label>
                            </div>
                        </div>
                        
                        <div class="row liquid-fields" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="volumePerBottle" class="form-label">Thể tích mỗi chai</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control" id="volumePerBottle" 
                                              name="volume_per_bottle" value="100">
                                        <select class="form-select" name="volume_unit" style="max-width: 100px;">
                                            <option value="ml" selected>ml</option>
                                            <option value="L">L</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tổng thể tích</label>
                                    <div class="form-control bg-light">
                                        <span id="totalVolume">0</span> ml
                                    </div>
                                    <small class="form-text text-muted">
                                        Số chai × thể tích mỗi chai
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu thuốc mới
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
    const volumePerBottle = document.getElementById('volumePerBottle');
    const volumeUnit = document.querySelector('select[name="volume_unit"]');
    const stockCount = document.getElementById('so_luong');
    const totalVolume = document.getElementById('totalVolume');
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
        
        updateTotalVolume();
    });
    
    // Calculate total volume
    function updateTotalVolume() {
        const volume = parseFloat(volumePerBottle.value) || 0;
        const count = parseInt(stockCount.value) || 0;
        const unit = volumeUnit.value;
        
        let totalVol = volume * count;
        if (unit === 'L') {
            totalVol = totalVol * 1000; // Convert to ml for display
        }
        
        totalVolume.textContent = totalVol.toFixed(2);
    }
    
    volumePerBottle.addEventListener('input', updateTotalVolume);
    stockCount.addEventListener('input', updateTotalVolume);
    volumeUnit.addEventListener('change', updateTotalVolume);
    
    // Initial calculation
    updateTotalVolume();
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>