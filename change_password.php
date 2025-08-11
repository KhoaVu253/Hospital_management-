<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra mật khẩu hiện tại
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!verifyPassword($current_password, $user['password'])) {
        showMessage('Mật khẩu hiện tại không đúng!', 'danger');
        header("Location: profile.php");
        exit();
    }
    
    // Kiểm tra mật khẩu mới
    if ($new_password !== $confirm_password) {
        showMessage('Mật khẩu xác nhận không khớp!', 'danger');
        header("Location: profile.php");
        exit();
    }
    
    if (strlen($new_password) < 6) {
        showMessage('Mật khẩu phải có ít nhất 6 ký tự!', 'danger');
        header("Location: profile.php");
        exit();
    }
    
    // Cập nhật mật khẩu
    $hashed_password = hashPassword($new_password);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        showMessage('Đổi mật khẩu thành công!', 'success');
    } else {
        showMessage('Có lỗi xảy ra khi đổi mật khẩu!', 'danger');
    }
    
    header("Location: profile.php");
    exit();
} else {
    header("Location: profile.php");
    exit();
}
?> 