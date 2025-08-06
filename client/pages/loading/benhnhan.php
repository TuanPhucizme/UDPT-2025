<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Quản lý Bệnh nhân</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php require_once '../../app/views/layouts/header.php'; ?>

  <div class="container py-5">
    <h2 class="text-center mb-4">📋 Quản lý Bệnh nhân</h2>
    
    <div class="row justify-content-center">
      <div class="col-md-4 d-grid mb-3">
        <a href="../../benhnhan/thembenhnhan.php" class="btn btn-success btn-lg">➕ Thêm Bệnh Nhân</a>
      </div>
      <div class="col-md-4 d-grid mb-3">
        <a href="../../benhnhan/timbenhnhan.php" class="btn btn-primary btn-lg">🔍 Tra Cứu Bệnh Nhân</a>
      </div>
    </div>
    
    <div class="text-center mt-4">
      <a href="../index.php" class="btn btn-outline-secondary">🏠 Quay về trang chủ</a>
    </div>
  </div>

  <?php require_once '../../app/views/layouts/footer.php'; ?>
</body>
</html>
