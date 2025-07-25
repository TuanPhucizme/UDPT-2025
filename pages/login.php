<?php
session_start();
include('../admin/config/config.php'); // Kết nối CSDL

$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = md5($_POST['password'] ?? '');

    $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($mysqli, $sql);
    $count = mysqli_num_rows($result);

    if ($count > 0) {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Quản lý bệnh viện</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow p-4 w-100" style="max-width: 400px;">
        <form method="POST" action="" autocomplete="off">
            <h2 class="text-center mb-4">Đăng nhập hệ thống</h2>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Nhập tên đăng nhập" required autofocus>
            </div>

            <div class="mb-3">
                <label for="inputPassword" class="form-label">Mật khẩu</label>
                <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
            </div>

            <button class="btn btn-primary w-100" type="submit" name="login">Đăng nhập</button>
            <p class="text-center text-muted mt-3 small pt-3"> <?= date("M d - Y") ?></p>
        </form>
    </div>
</div>

</body>
</html>
