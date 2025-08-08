<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Chuyển hướng nếu đã đăng nhập
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: ../admin/dashboard.php");
    } elseif (isDoctor()) {
        header("Location: ../doctor/dashboard.php");
    } else {
        header("Location: ../patient/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = sanitizeInput($_POST['address']);
    
    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        // Kiểm tra username và email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Tên đăng nhập hoặc email đã tồn tại!';
        } else {
            // Tạo tài khoản mới
            $hashed_password = hashPassword($password);
            $role = 'patient'; // Mặc định là bệnh nhân
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, date_of_birth, gender, address, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $username, $email, $hashed_password, $full_name, $phone, $date_of_birth, $gender, $address, $role);
            
            if ($stmt->execute()) {
                $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                // Xóa dữ liệu form
                $_POST = array();
            } else {
                $error = 'Có lỗi xảy ra! Vui lòng thử lại.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">Đăng ký tài khoản</h3>
                        <p class="text-muted">Tạo tài khoản để sử dụng dịch vụ</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-2"></i>Tên đăng nhập <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Mật khẩu <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Xác nhận mật khẩu <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">
                                <i class="fas fa-id-card me-2"></i>Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2"></i>Số điện thoại
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">
                                        <i class="fas fa-calendar me-2"></i>Ngày sinh
                                    </label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars me-2"></i>Giới tính
                                    </label>
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Chọn giới tính</option>
                                        <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                                        <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                        <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ
                            </label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Đăng ký
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Đã có tài khoản? 
                            <a href="login.php" class="text-decoration-none">Đăng nhập ngay</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 