<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đặt lịch khám</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-5">
  <h2 class="mb-4 text-center">Đặt Lịch Khám Bệnh</h2>

  <!-- Đặt lịch khám -->
  <form action="dat_lich.php" method="POST" class="border rounded p-4 mb-5" id="formDatLich">
    <h4>Thông tin bệnh nhân</h4>
    <div class="mb-3">
      <label for="hoten" class="form-label">Họ tên bệnh nhân</label>
      <input type="text" class="form-control" name="hoten" required>
    </div>

    <div class="mb-3">
      <label for="ngay_kham" class="form-label">Ngày khám</label>
      <input type="date" class="form-control" name="ngay_kham" required>
    </div>

    <div class="mb-3">
      <label for="gio_kham" class="form-label">Giờ khám</label>
      <input type="time" class="form-control" name="gio_kham" required>
    </div>

    <div class="mb-3">
      <label for="bacsi_id" class="form-label">Chọn bác sĩ</label>
      <select name="bacsi_id" class="form-select" required>
        <option value="">-- Chọn bác sĩ --</option>
        <option value="1">BS. Nguyễn Văn A - Nội khoa</option>
        <option value="2">BS. Trần Thị B - Tai Mũi Họng</option>
        <option value="3">BS. Lê Văn C - Nhi khoa</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="ghichu" class="form-label">Ghi chú</label>
      <textarea name="ghichu" class="form-control" rows="2"></textarea>
    </div>

    <div class="d-flex justify-content-between">
      <div>
        <button type="submit" class="btn btn-primary">Đặt lịch</button>
        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('formDatLich').reset()">Reset</button>
      </div>
      <a href="../pages/loading/lichkham.php" class="btn btn-outline-secondary">Quay lại</a>
    </div>
  </form>

  <!-- Bảng xác nhận lịch khám -->
  <h4>Xác nhận lịch khám cho bác sĩ</h4>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Bệnh nhân</th>
        <th>Ngày khám</th>
        <th>Giờ khám</th>
        <th>Bác sĩ</th>
        <th>Ghi chú</th>
        <th>Trạng thái</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <!-- Dữ liệu mẫu -->
      <tr>
        <td>Nguyễn Văn A</td>
        <td>2025-07-30</td>
        <td>09:00</td>
        <td>BS. Trần Thị B</td>
        <td>Khám định kỳ</td>
        <td><span class="badge bg-warning">Chờ xác nhận</span></td>
        <td>
          <button class="btn btn-success btn-sm">Xác nhận</button>
          <button class="btn btn-danger btn-sm">Hủy</button>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
</body>
</html>
