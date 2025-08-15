<?php
require_once('../configuration/configuration.php');

require_once(ROOT_PATH . '/configuration/access_control.php');

$error = '';

// Nếu người dùng đã đăng nhập, chuyển hướng họ ngay lập tức
if (isset($_SESSION['username'])) {
    // Dùng BASE_URL để tạo URL tuyệt đối, không bao giờ sai
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: " . BASE_URL . "/pages/admin/dashboard_admin.php");
            break;
        case 'doctor':
            header("Location: " . BASE_URL . "/pages/bacsi/dashboard_bacsi.php"); // Sửa lại đường dẫn nếu cần
            break;
        case 'pharmacist':
            header("Location: " . BASE_URL . "/pages/duocsi/dashboard_duocsi.php"); // Sửa lại đường dẫn nếu cần
            break;
        case 'receptionist':
            header("Location: " . BASE_URL . "/pages/letan/dashboard_letan.php"); // Sửa lại đường dẫn nếu cần
            break;
        default:
            session_destroy();
            header("Location: " . BASE_URL . "/pages/login.php");
            break;
    }
    exit();
}

// Nếu người dùng gửi form đăng nhập
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = md5($_POST['password'] ?? ''); 

    $sql = "SELECT username, role FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($mysqli, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Chuyển hướng sau khi đăng nhập thành công
        switch ($user['role']) {
            case 'admin':
                header("Location: " . BASE_URL . "/pages/admin/dashboard_admin.php");
                break;
            case 'bacsi':
                header("Location: " . BASE_URL . "/pages/bacsi/dashboard_bacsi.php");
                break;
            case 'duocsi':
                header("Location: " . BASE_URL . "/pages/duocsi/dashboard_duocsi.php");
                break;
            case 'letan':
                header("Location: " . BASE_URL . "/pages/letan/dashboard_letan.php");
                break;
            default:
                header("Location: " . BASE_URL . "/pages/login.php");
                break;
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4 w-100 card-blur" style="max-width: 450px;">
        <form method="POST" action="" autocomplete="off">
            <h2 class="text-center mb-4 fw-bold">
                <i class="fas fa-hospital-user me-2"></i>
                Đăng nhập
            </h2>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Sử dụng input-group để thêm icon cho Tên đăng nhập -->
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Nhập tên đăng nhập" required autofocus>
                </div>
            </div>

            <!-- Sử dụng input-group để thêm icon cho Mật khẩu -->
            <div class="mb-4">
                <label for="inputPassword" class="form-label">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                </div>
            </div>

            <!-- Thêm icon cho nút Đăng nhập -->
            <button class="btn btn-primary w-100 btn-lg" type="submit" name="login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Đăng nhập
            </button>

            <p class="text-center text-muted mt-3 small pt-3"> <?= date("M d - Y") ?></p>
        </form>
    </div>
</div>

</body>
</html>
