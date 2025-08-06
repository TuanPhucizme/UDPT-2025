<?php
// File: /configuration/access_control.php

// Luôn bắt đầu session ở đây để đảm bảo nó luôn chạy
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Kiểm tra xem người dùng đã đăng nhập và có quyền truy cập trang này không.
 *
 * @param array $allowed_roles Mảng chứa các vai trò được phép.
 */
function check_access(array $allowed_roles) {
    // 1. Kiểm tra đã đăng nhập chưa
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        // Dùng BASE_URL để tạo đường dẫn tuyệt đối, không bao giờ sai
        header("Location: " . BASE_URL . "/pages/login.php");
        exit();
    }

    // 2. Kiểm tra xem role của người dùng có nằm trong danh sách được phép không
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: " . BASE_URL . "/pages/access_denied.php");
        exit();
    }
}
?>