<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $username  = sanitizeInput($_POST['username'] ?? '');
    $email     = sanitizeInput($_POST['email'] ?? '');
    $phone     = sanitizeInput($_POST['phone'] ?? '');
    $gender    = sanitizeInput($_POST['gender'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address   = sanitizeInput($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
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
        if ($stmt->get_result()->fetch_assoc()) {
            $errors[] = 'Tên đăng nhập hoặc email đã tồn tại.';
        }
    }

    if (empty($errors)) {
        $hashed = hashPassword($password);
        $role = 'patient';
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role, full_name, phone, date_of_birth, gender, address) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssss', $username, $hashed, $email, $role, $full_name, $phone, $date_of_birth, $gender, $address);
        if ($stmt->execute()) {
            showMessage('Thêm bệnh nhân thành công!', 'success');
            header('Location: manage_patients.php');
            exit();
        } else {
            $errors[] = 'Không thể tạo tài khoản. Vui lòng thử lại.';
        }
    }
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>
<div class="container py-4">
    <a href="manage_patients.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Thêm bệnh nhân</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">Chọn</option>
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Xác nhận mật khẩu</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                    <a href="manage_patients.php" class="btn btn-secondary ms-2"><i class="fas fa-times me-2"></i>Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

