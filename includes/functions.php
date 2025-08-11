<?php
session_start();

// Kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Kiểm tra quyền admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Kiểm tra quyền bác sĩ
function isDoctor() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'doctor';
}

// Kiểm tra quyền bệnh nhân
function isPatient() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'patient';
}

// Chuyển hướng nếu chưa đăng nhập
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Chuyển hướng nếu không phải admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit();
    }
}

// Chuyển hướng nếu không phải bác sĩ
function requireDoctor() {
    requireLogin();
    if (!isDoctor()) {
        header("Location: ../index.php");
        exit();
    }
}

// Chuyển hướng nếu không phải bệnh nhân
function requirePatient() {
    requireLogin();
    if (!isPatient()) {
        header("Location: ../index.php");
        exit();
    }
}

// Hiển thị thông báo
function showMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Lấy và xóa thông báo
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Format ngày tháng
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Format ngày giờ
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Format giờ
function formatTime($datetime) {
    return date('H:i', strtotime($datetime));
}

// Format tiền tệ
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

// Tạo mật khẩu hash
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Kiểm tra mật khẩu
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Lọc dữ liệu đầu vào
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Tạo token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Kiểm tra token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Lấy thông tin người dùng
function getUserInfo($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Lấy danh sách bác sĩ
function getDoctors() {
    global $conn;
    $sql = "SELECT d.*, u.full_name, u.email, u.phone 
            FROM doctors d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.status = 'active'";
    return $conn->query($sql);
}

// Lấy thông tin bác sĩ
function getDoctorInfo($doctorId) {
    global $conn;
    $stmt = $conn->prepare("SELECT d.*, u.full_name, u.email, u.phone 
                           FROM doctors d 
                           JOIN users u ON d.user_id = u.id 
                           WHERE d.id = ?");
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Lấy lịch khám của bệnh nhân
function getPatientAppointments($patientId) {
    global $conn;
    $stmt = $conn->prepare("SELECT a.*, d.specialty, u.full_name as doctor_name 
                           FROM appointments a 
                           JOIN doctors d ON a.doctor_id = d.id 
                           JOIN users u ON d.user_id = u.id 
                           WHERE a.patient_id = ? 
                           ORDER BY a.appointment_date DESC");
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    return $stmt->get_result();
}

// Lấy lịch khám của bác sĩ
function getDoctorAppointments($doctorId) {
    global $conn;
    $stmt = $conn->prepare("SELECT a.*, u.full_name as patient_name, u.phone as patient_phone 
                           FROM appointments a 
                           JOIN users u ON a.patient_id = u.id 
                           WHERE a.doctor_id = ? 
                           ORDER BY a.appointment_date DESC");
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    return $stmt->get_result();
}

// Kiểm tra lịch khám có trùng không
function checkAppointmentConflict($doctorId, $appointmentDate, $excludeId = null) {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM appointments 
            WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'";
    if ($excludeId) {
        $sql .= " AND id != ?";
    }
    $stmt = $conn->prepare($sql);
    if ($excludeId) {
        $stmt->bind_param("isi", $doctorId, $appointmentDate, $excludeId);
    } else {
        $stmt->bind_param("is", $doctorId, $appointmentDate);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] > 0;
}

// Lấy đường dẫn cơ sở
function getBaseUrl() {
    $currentPath = $_SERVER['PHP_SELF'];
    $pathParts = explode('/', $currentPath);
    
    // Loại bỏ file hiện tại
    array_pop($pathParts);
    
    // Đếm số thư mục cần quay lại
    $backCount = 0;
    foreach ($pathParts as $part) {
        if ($part && $part != 'Hospital_management-') {
            $backCount++;
        }
    }
    
    $baseUrl = '';
    for ($i = 0; $i < $backCount; $i++) {
        $baseUrl .= '../';
    }
    
    return $baseUrl;
}

// Lấy đường dẫn logout
function getLogoutUrl() {
    $currentPath = $_SERVER['PHP_SELF'];
    $pathParts = explode('/', $currentPath);
    
    // Kiểm tra xem đang ở thư mục nào
    if (in_array('admin', $pathParts)) {
        return 'logout.php';
    } elseif (in_array('doctor', $pathParts)) {
        return 'logout.php';
    } elseif (in_array('patient', $pathParts)) {
        return 'logout.php';
    } elseif (in_array('auth', $pathParts)) {
        return 'logout.php';
    } else {
        return 'auth/logout.php';
    }
}
?> 