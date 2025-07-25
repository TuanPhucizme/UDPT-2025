<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tra Cứu Bệnh Nhân</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    function resetForm() {
      document.getElementById("searchForm").reset();
    }

    function goBack() {
      window.location.href = 'loading_benhnhan.php';
    }
  </script>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">🔍 Tra Cứu Bệnh Nhân</h2>

  <!-- Form tìm kiếm -->
  <form id="searchForm" method="GET" class="row g-3 align-items-end mb-4">
    <div class="col-md-6">
      <label for="keyword" class="form-label">Nhập tên bệnh nhân</label>
      <input type="text" class="form-control" name="keyword" placeholder="Ví dụ: Nguyễn Văn A">
    </div>
    <div class="d-flex justify-content-between align-items-center p-2">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('formTimBenhNhan').reset()">Reset</button>
    </div>
    <a href="../pages/loading/benhnhan.php" class="btn btn-outline-secondary">Quay lại</a>
    </div>
  </form>

  <!-- Bảng kết quả (giả lập dữ liệu) -->
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Mã BN</th>
        <th>Họ tên</th>
        <th>Ngày sinh</th>
        <th>Giới tính</th>
        <th>Địa chỉ</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>BN001</td>
        <td>Nguyễn Văn A</td>
        <td>1990-05-12</td>
        <td>Nam</td>
        <td>Quận 1, TP.HCM</td>
        <td><button class="btn btn-info btn-sm">Xem hồ sơ</button></td>
      </tr>
      <tr>
        <td>BN002</td>
        <td>Trần Thị B</td>
        <td>1985-09-30</td>
        <td>Nữ</td>
        <td>Quận 5, TP.HCM</td>
        <td><button class="btn btn-info btn-sm">Xem hồ sơ</button></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
