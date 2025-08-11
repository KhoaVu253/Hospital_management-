<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền bác sĩ
requireDoctor();

$message = '';
$error = '';

// Lấy thông tin bác sĩ hiện tại
$stmt = $conn->prepare("SELECT u.*, d.specialty, d.experience_years, d.education, d.license_number, d.consultation_fee 
                        FROM users u 
                        JOIN doctors d ON u.id = d.user_id 
                        WHERE u.id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $gender = sanitizeInput($_POST['gender']);
    $date_of_birth = sanitizeInput($_POST['date_of_birth']);
    $address = sanitizeInput($_POST['address']);
    $specialty = sanitizeInput($_POST['specialty']);
    $experience_years = (int)$_POST['experience_years'];
    $education = sanitizeInput($_POST['education']);
    $license_number = sanitizeInput($_POST['license_number']);
    $consultation_fee = (float)$_POST['consultation_fee'];

    // Kiểm tra email trùng lặp
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Email đã được sử dụng bởi tài khoản khác!";
    } else {
        // Cập nhật thông tin user
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, gender = ?, date_of_birth = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $full_name, $email, $phone, $gender, $date_of_birth, $address, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            // Cập nhật thông tin doctor
            $stmt = $conn->prepare("UPDATE doctors SET specialty = ?, experience_years = ?, education = ?, license_number = ?, consultation_fee = ? WHERE user_id = ?");
            $stmt->bind_param("sisdsi", $specialty, $experience_years, $education, $license_number, $consultation_fee, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = "Cập nhật thông tin thành công!";
                // Cập nhật session
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                // Lấy lại thông tin mới
                $stmt = $conn->prepare("SELECT u.*, d.specialty, d.experience_years, d.education, d.license_number, d.consultation_fee 
                                      FROM users u 
                                      JOIN doctors d ON u.id = d.user_id 
                                      WHERE u.id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $doctor = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Lỗi khi cập nhật thông tin bác sĩ!";
            }
        } else {
            $error = "Lỗi khi cập nhật thông tin cá nhân!";
        }
    }
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">\n    <div class="row">\n        <!-- Main content -->\n        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-user-edit me-2"></i>
                    Chỉnh sửa thông tin cá nhân
                </h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-md me-2"></i>
                                Thông tin bác sĩ
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                   value="<?php echo htmlspecialchars($doctor['full_name']); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($doctor['phone']); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Giới tính</label>
                                            <select class="form-select" id="gender" name="gender">
                                                <option value="male" <?php echo ($doctor['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                                                <option value="female" <?php echo ($doctor['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">Ngày sinh</label>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                                   value="<?php echo $doctor['date_of_birth']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="specialty" class="form-label">Chuyên khoa <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="specialty" name="specialty" 
                                                   value="<?php echo htmlspecialchars($doctor['specialty']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="experience_years" class="form-label">Số năm kinh nghiệm</label>
                                            <input type="number" class="form-control" id="experience_years" name="experience_years" 
                                                   value="<?php echo $doctor['experience_years']; ?>" min="0">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="consultation_fee" class="form-label">Phí khám bệnh (VNĐ)</label>
                                            <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" 
                                                   value="<?php echo $doctor['consultation_fee']; ?>" min="0" step="1000">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="education" class="form-label">Học vấn</label>
                                    <textarea class="form-control" id="education" name="education" rows="3"><?php echo htmlspecialchars($doctor['education']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="license_number" class="form-label">Số chứng chỉ hành nghề</label>
                                    <input type="text" class="form-control" id="license_number" name="license_number" 
                                           value="<?php echo htmlspecialchars($doctor['license_number']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($doctor['address']); ?></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật thông tin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Thông tin tài khoản</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($doctor['username']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Vai trò</label>
                                <input type="text" class="form-control" value="Bác sĩ" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Ngày tạo tài khoản</label>
                                <input type="text" class="form-control" value="<?php echo formatDateTime($doctor['created_at']); ?>" readonly>
                            </div>
                            
                            <a href="change_password.php" class="btn btn-warning btn-sm w-100">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
