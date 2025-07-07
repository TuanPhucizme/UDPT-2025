<link rel="stylesheet" href="../css/them.css">
<link rel="stylesheet" href="../css/benhnhan.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <h2 class="text-center mb-4">Thêm Bệnh nhân mới</h2>

    <form action="../benhnhan/xulybenhnhan.php" method="POST" class="border p-4 shadow rounded bg-light">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="ten" class="form-label">Tên</label>
                <input type="text" class="form-control" name="ten" placeholder="Tên bệnh nhân" required>
            </div>
            <div class="col-md-6">
                <label for="ngaysinh" class="form-label">Ngày Sinh</label>
                <input type="date" class="form-control" name="ngaysinh" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="gioitinh" class="form-label">Giới tính</label>
                <select class="form-select" name="gioitinh" required>
                    <option value="">-- Chọn giới tính --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="sdt" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" name="sdt" placeholder="Số điện thoại" required>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" name="them" class="btn btn-primary me-2">Thêm bệnh nhân</button>
            <button type="button" name="back" class="btn btn-secondary" onclick="window.location.href='../pages/index.php'">Quay về</button>
        </div>
    </form>

    <h2 class="text-center my-5">Danh sách Bệnh nhân</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Tên</th>
                    <th scope="col">Ngày sinh</th>
                    <th scope="col">Giới tính</th>
                    <th scope="col">SĐT</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include('../admin/config/config.php');
                $sql = "SELECT * FROM benhnhan ORDER BY ID";
                $result = mysqli_query($mysqli, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td class='text-center'>{$row['ID']}</td>";
                    echo "<td>{$row['hoten']}</td>";
                    echo "<td>{$row['ngaysinh']}</td>";
                    echo "<td>{$row['gioitinh']}</td>";
                    echo "<td>{$row['sdt']}</td>";
                    // echo "<td>
                    //         <a href='sua.php?id={$row['ID']}' class='btn btn-warning btn-sm'>✏️ Sửa</a>
                    //         <a href='xoa.php?id={$row['ID']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Bạn có chắc chắn muốn xóa?')\">🗑️ Xóa</a>
                    //       </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
