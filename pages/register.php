<?php
session_start();

$error = '';
$success = '';

// Đường dẫn đầy đủ đến file users.json trong thư mục data
$usersFile = __DIR__ . '/../data/users.json';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Nếu file không tồn tại, tạo file rỗng
        if (!file_exists($usersFile)) {
            file_put_contents($usersFile, json_encode([]));
        }

        // Đọc dữ liệu từ file
        $jsonData = file_get_contents($usersFile);
        $users = json_decode($jsonData, true);

        // Nếu dữ liệu không hợp lệ, gán mảng rỗng
        if (!is_array($users)) {
            $users = [];
        }

        // Kiểm tra trùng username
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $error = "Tên tài khoản đã tồn tại!";
                break;
            }
        }

        // Nếu không trùng, thêm tài khoản mới
        if (!$error) {
            $users[] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
            $success = "Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Quản lý bệnh viện</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <form class="register-form" method="POST">
        <h2>Đăng ký tài khoản</h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng ký</button>
        <p style="margin-top: 10px;">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </form>
</body>
</html>
