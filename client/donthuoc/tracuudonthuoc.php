<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tra cứu đơn thuốc</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
  <h2 class="mb-4 text-center">Tra cứu đơn thuốc bệnh nhân</h2>

  <form method="GET" action="tra_cuu_don_thuoc.php" class="row g-3 mb-4">
    <div class="col-md-5">
      <input type="text" name="hoten" class="form-control" placeholder="Nhập tên bệnh nhân">
    </div>
    <div class="col-md-4">
      <select name="trang_thai" class="form-select">
        <option value="">-- Tất cả trạng thái --</option>
        <option value="chua">Chưa lấy</option>
        <option value="da">Đã lấy</option>
      </select>
    </div>
    <div class="d-flex justify-content-between gap-2">
      <div>
      <button type="submit" class="btn btn-success">Tra Cứu</button>
      <button type="reset" class="btn btn-secondary">Reset</button>
      </div>
      <a href="../pages/loading/donthuoc.php" class="btn btn-outline-secondary">Quay lại</a>
    </div>
  </form>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Bệnh nhân</th>
        <th>Bác sĩ</th>
        <th>Đơn thuốc</th>
        <th>Trạng thái</th>
      </tr>
    </thead>
    <tbody>
      <!-- Ví dụ dữ liệu -->
      <tr>
        <td>Nguyễn Văn A</td>
        <td>BS. Trần Thị B</td>
        <td>Paracetamol 500mg, ngày 3 lần</td>
        <td><span class="badge bg-success">Đã lấy</span></td>
      </tr>
      <tr>
        <td>Phạm Thị C</td>
        <td>BS. Lê Văn D</td>
        <td>Amoxicillin 250mg, sáng và chiều</td>
        <td><span class="badge bg-warning text-dark">Chưa lấy</span></td>
      </tr>
    </tbody>
  </table>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
</body>
</html>
