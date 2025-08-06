<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('ROOT_PATH', dirname(__DIR__));

define('BASE_URL', 'http://localhost/UDPT-2025/client');

/**
 * Phần kết nối Cơ sở dữ liệu
 */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hospital-data"; // Tên CSDL của bạn

// Kết nối đến cơ sở dữ liệu
$mysqli = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($mysqli -> connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
}

$mysqli->set_charset("utf8mb4");

?>