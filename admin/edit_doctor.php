<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$doctorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT d.*, u.full_name, u.email, u.phone, u.gender 
                        FROM doctors d 
                        JOIN users u ON d.user_id = u.id 
                        WHERE d.id = ? LIMIT 1");
$stmt->bind_param('i', $doctorId);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    showMessage('Không tìm thấy bác sĩ', 'warning');
    header('Location: manage_doctors.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $specialty = sanitizeInput($_POST['specialty'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $education = sanitizeInput($_POST['education'] ?? '');
    $license_number = sanitizeInput($_POST['license_number'] ?? '');
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 200000);
    $status = in_array(($_POST['status'] ?? 'active'), ['active','inactive']) ? $_POST['status'] : 'active';

    if ($full_name === '' || $email === '' || $specialty === '') {
        $errors[] = 'Vui lòng nhập đầy đủ các trường bắt buộc.';
    }

    // Kiểm tra trùng email của user khác
    $stmt = $conn->prepare("SELECT u.id FROM users u JOIN doctors d2 ON u.id = d2.user_id WHERE u.email = ? AND d2.id <> ? LIMIT 1");
    $stmt->bind_param('si', $email, $doctorId);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $errors[] = 'Email đã được sử dụng bởi tài khoản khác.';
    }

    if (empty($errors)) {
        // Cập nhật users
        $stmt = $conn->prepare("UPDATE users u JOIN doctors d ON u.id = d.user_id SET u.full_name=?, u.email=?, u.phone=?, u.gender=?, d.specialty=?, d.experience_years=?, d.education=?, d.license_number=?, d.consultation_fee=?, d.status=? WHERE d.id=?");
        $stmt->bind_param('sssssisdsi', $full_name, $email, $phone, $gender, $specialty, $experience_years, $education, $license_number, $consultation_fee, $status, $doctorId);
        if ($stmt->execute()) {
            showMessage('Cập nhật thông tin bác sĩ thành công!', 'success');
            header('Location: view_doctor.php?id=' . $doctorId);
            exit();
        } else {
            $errors[] = 'Không thể cập nhật thông tin. Vui lòng thử lại.';
        }
    }
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>
<div class="container py-4">
    <a href="manage_doctors.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa bác sĩ</h5>
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
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? $doctor['full_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? $doctor['email']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? $doctor['phone']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">Chọn</option>
                        <option value="male" <?php echo (($doctor['gender'] ?? '')==='male')?'selected':''; ?>>Nam</option>
                        <option value="female" <?php echo (($doctor['gender'] ?? '')==='female')?'selected':''; ?>>Nữ</option>
                        <option value="other" <?php echo (($doctor['gender'] ?? '')==='other')?'selected':''; ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kinh nghiệm (năm)</label>
                    <input type="number" name="experience_years" min="0" class="form-control" value="<?php echo htmlspecialchars($_POST['experience_years'] ?? $doctor['experience_years']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Chuyên khoa</label>
                    <input type="text" name="specialty" class="form-control" value="<?php echo htmlspecialchars($_POST['specialty'] ?? $doctor['specialty']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số chứng chỉ</label>
                    <input type="text" name="license_number" class="form-control" value="<?php echo htmlspecialchars($_POST['license_number'] ?? $doctor['license_number']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phí khám bệnh (VNĐ)</label>
                    <input type="number" min="0" step="1000" name="consultation_fee" class="form-control" value="<?php echo htmlspecialchars($_POST['consultation_fee'] ?? $doctor['consultation_fee']); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Học vấn</label>
                    <textarea name="education" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['education'] ?? $doctor['education']); ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" <?php echo (($doctor['status'] ?? 'active')==='active')?'selected':''; ?>>Đang làm việc</option>
                        <option value="inactive" <?php echo (($doctor['status'] ?? '')==='inactive')?'selected':''; ?>>Ngừng làm việc</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                    <a href="view_doctor.php?id=<?php echo $doctorId; ?>" class="btn btn-secondary ms-2"><i class="fas fa-eye me-2"></i>Xem</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

