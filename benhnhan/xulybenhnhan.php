<?php
include('../admin/config/config.php');

$ten = $_POST['ten'] ?? '';
$ngaysinh = $_POST['ngaysinh'] ?? '';
$gioitinh = $_POST['gioitinh'] ?? '';
$sdt = $_POST['sdt'] ?? '';

if (isset($_POST['them'])) {
    if ($ten === '' || $ngaysinh === '' || $gioitinh === '' || $sdt === '') {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!'); window.history.back();</script>";
        exit();
    }

    // Chuẩn bị câu truy vấn an toàn
    $stmt = mysqli_prepare($mysqli, "INSERT INTO benhnhan (hoten, gioitinh, ngaysinh, sdt) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssss', $ten, $gioitinh, $ngaysinh, $sdt);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: thembenhnhan.php?success=1");
    } else {
        echo "<script>alert('Lỗi khi thêm bệnh nhân'); window.history.back();</script>";
    }

    mysqli_stmt_close($stmt);
}

?>
