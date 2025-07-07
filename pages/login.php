<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng đến index
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$error = '';

// Xử lý khi gửi form đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        // Đọc dữ liệu từ file users.json
        $users = json_decode(file_get_contents("../data/users.json"), true);
        $found = false;

        // Kiểm tra tài khoản tồn tại và mật khẩu đúng
        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Sai tên đăng nhập hoặc mật khẩu!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Quản lý bệnh viện</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <form class="login-form" method="POST">
        <h2>Đăng nhập</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng nhập</button>
        <p style="margin-top: 10px;">Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
    </form>
</body>
</html>
