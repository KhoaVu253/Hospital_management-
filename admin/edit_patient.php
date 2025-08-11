<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$patientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin bệnh nhân
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'patient' LIMIT 1");
$stmt->bind_param('i', $patientId);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    showMessage('Không tìm thấy bệnh nhân', 'warning');
    header('Location: manage_patients.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address = sanitizeInput($_POST['address'] ?? '');

    if ($full_name === '' || $email === '') {
        $errors[] = 'Họ tên và Email là bắt buộc.';
    }

    // Kiểm tra trùng email với user khác
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    $stmt->bind_param('si', $email, $patientId);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $errors[] = 'Email đã được sử dụng bởi tài khoản khác.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, gender=?, date_of_birth=?, address=? WHERE id=? AND role='patient'");
        $stmt->bind_param('ssssssi', $full_name, $email, $phone, $gender, $date_of_birth, $address, $patientId);
        if ($stmt->execute()) {
            showMessage('Cập nhật thông tin bệnh nhân thành công!', 'success');
            header('Location: view_patient.php?id=' . $patientId);
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
    <a href="manage_patients.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa bệnh nhân</h5>
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
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? $patient['full_name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? $patient['email']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? $patient['phone']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giới tính</label>
                    <select name="gender" class="form-select">
                        <option value="">Chọn</option>
                        <option value="male" <?php echo (($patient['gender'] ?? '')==='male')?'selected':''; ?>>Nam</option>
                        <option value="female" <?php echo (($patient['gender'] ?? '')==='female')?'selected':''; ?>>Nữ</option>
                        <option value="other" <?php echo (($patient['gender'] ?? '')==='other')?'selected':''; ?>>Khác</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? $patient['date_of_birth']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Địa chỉ</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? $patient['address']); ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                    <a href="view_patient.php?id=<?php echo $patientId; ?>" class="btn btn-secondary ms-2"><i class="fas fa-eye me-2"></i>Xem</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

