<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Chỉ admin được phép
requireAdmin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF (tùy chọn nếu muốn bật)
    // if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    //     $errors[] = 'Phiên làm việc không hợp lệ! Vui lòng thử lại.';
    // }

    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $specialty = sanitizeInput($_POST['specialty'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $education = sanitizeInput($_POST['education'] ?? '');
    $license_number = sanitizeInput($_POST['license_number'] ?? '');
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 200000);
    $status = in_array(($_POST['status'] ?? 'active'), ['active','inactive']) ? $_POST['status'] : 'active';

    // Validate cơ bản
    if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '' || $specialty === '') {
        $errors[] = 'Vui lòng nhập đầy đủ các trường bắt buộc.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }

    // Kiểm tra trùng username/email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        if ($exists) {
            $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
        }
    }

    if (empty($errors)) {
        // Tạo user role doctor
        $hashed = hashPassword($password);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, full_name, phone, date_of_birth, gender) VALUES (?,?,?,?,?,?,?,?)");
        $role = 'doctor';
        $stmt->bind_param('ssssssss', $username, $hashed, $email, $role, $full_name, $phone, $date_of_birth, $gender);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;

            // Tạo bản ghi doctor
            $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialty, experience_years, education, license_number, consultation_fee, status) VALUES (?,?,?,?,?,?,?)");
            $stmt2->bind_param('isissds', $userId, $specialty, $experience_years, $education, $license_number, $consultation_fee, $status);

            if ($stmt2->execute()) {
                showMessage('Thêm bác sĩ thành công!', 'success');
                header('Location: manage_doctors.php');
                exit();
            } else {
                // Rollback: xóa user nếu tạo doctors thất bại
                $conn->query("DELETE FROM users WHERE id = " . (int)$userId);
                $errors[] = 'Không thể tạo hồ sơ bác sĩ. Vui lòng thử lại.';
            }
        } else {
            $errors[] = 'Không thể tạo tài khoản người dùng.';
        }
    }
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">\n    <div class="row">\n        <!-- Main content -->\n        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-plus me-2"></i>Thêm bác sĩ</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="row g-4">
                <div class="col-12">
                    <h5 class="mb-3">Thông tin tài khoản</h5>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">Chọn</option>
                        <option value="male" <?php echo (($_POST['gender'] ?? '')==='male')?'selected':''; ?>>Nam</option>
                        <option value="female" <?php echo (($_POST['gender'] ?? '')==='female')?'selected':''; ?>>Nữ</option>
                        <option value="other" <?php echo (($_POST['gender'] ?? '')==='other')?'selected':''; ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <div class="col-12 mt-4">
                    <h5 class="mb-3">Thông tin chuyên môn</h5>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Chuyên khoa <span class="text-danger">*</span></label>
                    <input type="text" name="specialty" class="form-control" required value="<?php echo htmlspecialchars($_POST['specialty'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kinh nghiệm (năm)</label>
                    <input type="number" min="0" name="experience_years" class="form-control" value="<?php echo htmlspecialchars($_POST['experience_years'] ?? '0'); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số chứng chỉ</label>
                    <input type="text" name="license_number" class="form-control" value="<?php echo htmlspecialchars($_POST['license_number'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phí khám bệnh (VNĐ) <span class="text-danger">*</span></label>
                    <input type="number" min="0" step="1000" name="consultation_fee" class="form-control" required value="<?php echo htmlspecialchars($_POST['consultation_fee'] ?? '200000'); ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Học vấn</label>
                    <textarea name="education" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['education'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo (($_POST['status'] ?? 'active')==='active')?'selected':''; ?>>Đang làm việc</option>
                        <option value="inactive" <?php echo (($_POST['status'] ?? '')==='inactive')?'selected':''; ?>>Ngừng làm việc</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                    <a href="manage_doctors.php" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

