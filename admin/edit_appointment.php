<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';
$error = '';

// Lấy thông tin lịch khám
if (isset($_GET['id'])) {
    $appointment_id = (int)$_GET['id'];
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $patient_id = (int)$_POST['patient_id'];
        $doctor_id = (int)$_POST['doctor_id'];
        $appointment_date = $_POST['appointment_date'];
        $appointment_time = $_POST['appointment_time'];
        $status = $_POST['status'];
        $notes = trim($_POST['notes']);
        
        // Kết hợp ngày và giờ
        $datetime = $appointment_date . ' ' . $appointment_time;
        
        // Cập nhật lịch khám
        $sql = "UPDATE appointments SET 
                patient_id = ?, 
                doctor_id = ?, 
                appointment_date = ?, 
                status = ?, 
                notes = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssi", $patient_id, $doctor_id, $datetime, $status, $notes, $appointment_id);
        
        if ($stmt->execute()) {
            $message = "Cập nhật lịch khám thành công!";
        } else {
            $error = "Lỗi khi cập nhật lịch khám: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Lấy thông tin lịch khám hiện tại
    $sql = "SELECT * FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: manage_appointments.php");
        exit();
    }
    
    $appointment = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: manage_appointments.php");
    exit();
}

// Lấy danh sách bệnh nhân
$patients_sql = "SELECT id, full_name FROM users WHERE role = 'patient' ORDER BY full_name";
$patients_result = $conn->query($patients_sql);

// Lấy danh sách bác sĩ
$doctors_sql = "SELECT d.id, u.full_name, d.specialty FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY u.full_name";
$doctors_result = $conn->query($doctors_sql);

// Tách ngày và giờ
$appointment_datetime = new DateTime($appointment['appointment_date']);
$appointment_date = $appointment_datetime->format('Y-m-d');
$appointment_time = $appointment_datetime->format('H:i');

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row"><main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Chỉnh sửa lịch khám</h1>
                <a href="manage_appointments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
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

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patient_id" class="form-label">Bệnh nhân</label>
                                    <select class="form-select" id="patient_id" name="patient_id" required>
                                        <option value="">Chọn bệnh nhân</option>
                                        <?php while ($patient = $patients_result->fetch_assoc()): ?>
                                            <option value="<?php echo $patient['id']; ?>" 
                                                    <?php echo ($patient['id'] == $appointment['patient_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($patient['full_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="doctor_id" class="form-label">Bác sĩ</label>
                                    <select class="form-select" id="doctor_id" name="doctor_id" required>
                                        <option value="">Chọn bác sĩ</option>
                                        <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                                            <option value="<?php echo $doctor['id']; ?>" 
                                                    <?php echo ($doctor['id'] == $appointment['doctor_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($doctor['full_name'] . ' - ' . $doctor['specialty']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Ngày khám</label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                           value="<?php echo $appointment_date; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_time" class="form-label">Giờ khám</label>
                                    <input type="time" class="form-control" id="appointment_time" name="appointment_time" 
                                           value="<?php echo $appointment_time; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="pending" <?php echo ($appointment['status'] == 'pending') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                        <option value="confirmed" <?php echo ($appointment['status'] == 'confirmed') ? 'selected' : ''; ?>>Đã xác nhận</option>
                                        <option value="completed" <?php echo ($appointment['status'] == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="cancelled" <?php echo ($appointment['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Ghi chú</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($appointment['notes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
