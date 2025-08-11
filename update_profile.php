<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = sanitizeInput($_POST['full_name']);
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = sanitizeInput($_POST['address']);
    
    // Kiểm tra username và email có bị trùng không
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        showMessage('Tên đăng nhập hoặc email đã tồn tại!', 'danger');
        header("Location: profile.php");
        exit();
    }
    
    // Cập nhật thông tin
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, username = ?, email = ?, phone = ?, date_of_birth = ?, gender = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $full_name, $username, $email, $phone, $date_of_birth, $gender, $address, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        showMessage('Cập nhật hồ sơ thành công!', 'success');
    } else {
        showMessage('Có lỗi xảy ra khi cập nhật hồ sơ!', 'danger');
    }
    
    header("Location: profile.php");
    exit();
} else {
    header("Location: profile.php");
    exit();
}
?> 