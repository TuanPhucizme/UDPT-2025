<?php

// Simplified header - no nav or other components that might need services
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo trì - Hệ thống quản lý phòng khám</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fc;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .maintenance-icon {
            font-size: 4rem;
            color: #e74a3b;
        }
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5 text-center">
                        <div class="maintenance-icon mb-4">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h1 class="h4 mb-3">Hệ thống đang bảo trì</h1>
                        <p class="text-muted mb-4">Một số dịch vụ quan trọng hiện không khả dụng. Đội kỹ thuật của chúng tôi đang khắc phục sự cố.</p>
                        
                        <?php if (isset($_SESSION['maintenance']) && !empty($_SESSION['maintenance']['services'])): ?>
                        <div class="alert alert-danger text-start">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Các dịch vụ không khả dụng:</h6>
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['maintenance']['services'] as $service): ?>
                                <li><?= htmlspecialchars($service['name']) ?> - <?= htmlspecialchars($service['error']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="/maintenance-check" class="btn btn-primary"><i class="fas fa-sync-alt me-1"></i> Kiểm tra lại</a>
                            <a href="/auth/logout" class="btn btn-outline-secondary ms-2"><i class="fas fa-sign-out-alt me-1"></i> Đăng xuất</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>