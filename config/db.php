<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "hospital_management";

// Tạo kết nối
$conn = new mysqli($host, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Đặt charset UTF-8
$conn->set_charset("utf8");
?> 