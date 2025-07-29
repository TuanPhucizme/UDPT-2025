<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tìm kiếm lịch khám</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5 mt-5">
  <h2 class="mb-4 text-center">Tìm Kiếm Lịch Khám</h2>

  <!-- Form tìm kiếm -->
  <form method="GET" action="lichkham_search.php" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="keyword" class="form-control" placeholder="Tên bệnh nhân hoặc bác sĩ">
    </div>
    <div class="col-md-3">
      <input type="date" name="ngay_kham" class="form-control">
    </div>
    <div class="col-md-3">
      <select name="trang_thai" class="form-select">
        <option value="">-- Trạng thái --</option>
        <option value="cho">Chờ xác nhận</option>
        <option value="xacnhan">Đã xác nhận</option>
        <option value="huy">Đã hủy</option>
      </select>
    </div>
    <div class="d-flex justify-content-between p-2">
      <div>
        <button type="submit" class="btn btn-primary">Đặt lịch</button>
        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('formDatLich').reset()">Reset</button>
      </div>
      <a href="../pages/loading/lichkham.php" class="btn btn-outline-secondary">Quay lại</a>
    </div>
  </form>

  <!-- Kết quả -->
  <h5 class="mb-3">Kết quả tìm kiếm</h5>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Bệnh nhân</th>
        <th>Bác sĩ</th>
        <th>Ngày khám</th>
        <th>Giờ</th>
        <th>Ghi chú</th>
        <th>Trạng thái</th>
      </tr>
    </thead>
    <tbody>
      <!-- Dữ liệu mẫu -->
      <tr>
        <td>Nguyễn Văn A</td>
        <td>BS. Trần Thị B</td>
        <td>2025-07-30</td>
        <td>09:00</td>
        <td>Kiểm tra tai mũi họng</td>
        <td><span class="badge bg-success">Đã xác nhận</span></td>
      </tr>
      <tr>
        <td>Phạm Thị C</td>
        <td>BS. Nguyễn Văn A</td>
        <td>2025-07-29</td>
        <td>10:30</td>
        <td>Khám theo yêu cầu</td>
        <td><span class="badge bg-warning">Chờ xác nhận</span></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
