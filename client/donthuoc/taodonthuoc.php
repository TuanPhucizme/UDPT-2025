<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tạo đơn thuốc</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .thuoc-item { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 6px; }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Tạo đơn thuốc</h2>

  <form action="luu_don_thuoc.php" method="POST">
    <div class="mb-3">
      <label class="form-label">Tên bệnh nhân</label>
      <input type="text" class="form-control" name="hoten_benhnhan" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Tên bác sĩ</label>
      <input type="text" class="form-control" name="bacsi" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Chọn thuốc</label>
      <select id="thuocSelect" class="form-select" multiple>
        <option value="Paracetamol">Paracetamol</option>
        <option value="Amoxicillin">Amoxicillin</option>
        <option value="Vitamin C">Vitamin C</option>
        <option value="Ibuprofen">Ibuprofen</option>
        <option value="Clorpheniramin">Clorpheniramin</option>
      </select>
    </div>

    <div id="thuocDetails"></div>

    <div class="mb-3">
      <label class="form-label">Tình trạng</label>
      <select name="trang_thai" class="form-select">
        <option value="chua">Chưa lấy</option>
        <option value="da">Đã lấy</option>
      </select>
    </div>

    <div class="d-flex justify-content-between">
      <div>
      <button type="submit" class="btn btn-success">Lưu đơn thuốc</button>
      <button type="reset" class="btn btn-secondary">Reset</button>
      </div>
      <a href="../pages/loading/donthuoc.php" class="btn btn-outline-secondary">Quay lại</a>
    </div>
  </form>
</div>

<!-- Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    $('#thuocSelect').select2({ placeholder: "Tìm và chọn thuốc", width: '100%' });

    function renderThuocFields(selected) {
      const container = $('#thuocDetails');
      container.html('');
      selected.forEach(thuoc => {
        const html = `
          <div class="thuoc-item">
            <input type="hidden" name="thuoc[]" value="${thuoc}">
            <strong>${thuoc}</strong>
            <div class="mt-2">
              <label>Liều lượng / 1 lần:</label>
              <input type="text" name="lieu_luong[]" class="form-control mb-2" required>
              <label>Thời gian uống:</label>
              <input type="text" name="thoi_gian_uong[]" class="form-control" required>
            </div>
          </div>`;
        container.append(html);
      });
    }

    $('#thuocSelect').on('change', function() {
      const selected = $(this).val() || [];
      renderThuocFields(selected);
    });
  });
</script>
</body>
</html>
