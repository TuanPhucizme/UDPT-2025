<?php
// filepath: d:\xampp\htdocs\UDPT\UDPT-2025\client\app\views\errors\500.php
require_once '../app/views/layouts/header-minimal.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0 mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                        <h1 class="h3 mb-3">Lỗi máy chủ</h1>
                        <p class="text-muted">Đã xảy ra lỗi khi xử lý yêu cầu của bạn.</p>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="/" class="btn btn-primary me-2"><i class="fas fa-home me-1"></i> Trang chủ</a>
                        <button onclick="history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Quay lại</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer-minimal.php'; ?>