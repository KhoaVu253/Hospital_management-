<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền bệnh nhân
requirePatient();

$message = getMessage();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = sanitizeInput($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $symptoms = sanitizeInput($_POST['symptoms']);
    $notes = sanitizeInput($_POST['notes']);
    
    // Kiểm tra dữ liệu đầu vào
    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc!';
    } else {
        // Kết hợp ngày và giờ
        $appointment_datetime = $appointment_date . ' ' . $appointment_time;
        
        // Kiểm tra ngày khám không được trong quá khứ
        if (strtotime($appointment_datetime) <= time()) {
            $error = 'Ngày khám không được trong quá khứ!';
        } else {
            // Kiểm tra lịch khám có trùng không
            if (checkAppointmentConflict($doctor_id, $appointment_datetime)) {
                $error = 'Bác sĩ đã có lịch khám vào thời gian này! Vui lòng chọn thời gian khác.';
            } else {
                // Tạo lịch khám mới
                $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, symptoms, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("iisss", $_SESSION['user_id'], $doctor_id, $appointment_datetime, $symptoms, $notes);
                
                if ($stmt->execute()) {
                    showMessage('Đặt lịch khám thành công! Vui lòng chờ xác nhận từ bác sĩ.', 'success');
                    header("Location: my_appointments.php");
                    exit();
                } else {
                    $error = 'Có lỗi xảy ra! Vui lòng thử lại.';
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="book_appointment.php">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Đặt lịch khám
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Lịch khám của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical_history.php">
                            <i class="fas fa-history me-2"></i>
                            Lịch sử khám bệnh
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_bills.php">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Hóa đơn của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>
                            Hồ sơ cá nhân
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-calendar-plus me-2"></i>
                    Đặt lịch khám
                </h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Thông tin đặt lịch khám
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="doctor_id" class="form-label">
                                                <i class="fas fa-user-md me-2"></i>Chọn bác sĩ <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                                <option value="">Chọn bác sĩ</option>
                                                <?php
                                                $sql = "SELECT d.*, u.full_name, u.email, u.phone 
                                                        FROM doctors d 
                                                        JOIN users u ON d.user_id = u.id 
                                                        WHERE d.status = 'active' 
                                                        ORDER BY u.full_name";
                                                $result = $conn->query($sql);
                                                while ($row = $result->fetch_assoc()):
                                                ?>
                                                <option value="<?php echo $row['id']; ?>" 
                                                        <?php echo (isset($_POST['doctor_id']) && $_POST['doctor_id'] == $row['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['full_name']); ?> - <?php echo htmlspecialchars($row['specialty']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="appointment_date" class="form-label">
                                                <i class="fas fa-calendar me-2"></i>Ngày khám <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                                   value="<?php echo isset($_POST['appointment_date']) ? $_POST['appointment_date'] : ''; ?>" 
                                                   min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="appointment_time" class="form-label">
                                                <i class="fas fa-clock me-2"></i>Giờ khám <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="appointment_time" name="appointment_time" required>
                                                <option value="">Chọn giờ</option>
                                                <option value="08:00:00" <?php echo (isset($_POST['appointment_time']) && $_POST['appointment_time'] == '08:00:00') ? 'selected' : ''; ?>>08:00</option>
                                                <option value="09:00:00" <?php echo (isset($_POST['appointment_time']) && $_POST['appointment_time'] == '09:00:00') ? 'selected' : ''; ?>>09:00</option>
                                                <option value="10:00:00" <?php echo (isset($_POST['appointment_time']) && $_POST['appointment_time'] == '10:00:00') ? 'selected' : ''; ?>>10:00</option>
                                                <option value="14:00:00" <?php echo (isset($_POST['appointment_time']) && $_POST['appointment_time'] == '14:00:00') ? 'selected' : ''; ?>>14:00</option>
                                                <option value="15:00:00" <?php echo (isset($_POST['appointment_time']) && $_POST['appointment_time'] == '15:00:00') ? 'selected' : ''; ?>>15:00</option>
                                                <option value="16:00:00" <?php echo (isset($_POST['appointment_time']) && $_POST['appointment_time'] == '16:00:00') ? 'selected' : ''; ?>>16:00</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="symptoms" class="form-label">
                                        <i class="fas fa-notes-medical me-2"></i>Triệu chứng
                                    </label>
                                    <textarea class="form-control" id="symptoms" name="symptoms" rows="3" 
                                              placeholder="Mô tả các triệu chứng bạn đang gặp phải..."><?php echo isset($_POST['symptoms']) ? htmlspecialchars($_POST['symptoms']) : ''; ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note me-2"></i>Ghi chú thêm
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2" 
                                              placeholder="Ghi chú thêm nếu cần..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle me-2"></i>
                                Hướng dẫn
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-lightbulb me-2"></i>Lưu ý khi đặt lịch khám:</h6>
                                <ul class="mb-0">
                                    <li>Chọn bác sĩ phù hợp với chuyên khoa cần khám</li>
                                    <li>Ngày khám phải từ hôm nay trở đi</li>
                                    <li>Giờ khám từ 8:00 - 16:00 (trừ 12:00-14:00)</li>
                                    <li>Mô tả triệu chứng để bác sĩ chuẩn bị tốt hơn</li>
                                    <li>Lịch khám sẽ được xác nhận trong vòng 24h</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Quy định:</h6>
                                <ul class="mb-0">
                                    <li>Đến trước giờ hẹn 15 phút</li>
                                    <li>Mang theo giấy tờ tùy thân</li>
                                    <li>Có thể hủy lịch khám trước 24h</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch khám gần đây -->
                    <div class="card shadow mt-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar-check me-2"></i>
                                Lịch khám gần đây
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT a.*, d.specialty, u.full_name as doctor_name 
                                    FROM appointments a 
                                    JOIN doctors d ON a.doctor_id = d.id 
                                    JOIN users u ON d.user_id = u.id 
                                    WHERE a.patient_id = ? 
                                    ORDER BY a.appointment_date DESC 
                                    LIMIT 3";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                            ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo htmlspecialchars($row['doctor_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['specialty']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small><?php echo formatDateTime($row['appointment_date']); ?></small>
                                        <br>
                                        <?php
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'pending':
                                                $status_class = 'badge bg-warning';
                                                break;
                                            case 'confirmed':
                                                $status_class = 'badge bg-info';
                                                break;
                                            case 'completed':
                                                $status_class = 'badge bg-success';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'badge bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <p class="text-muted text-center">Chưa có lịch khám nào</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Kiểm tra lịch khám trùng khi thay đổi bác sĩ hoặc thời gian
document.getElementById('doctor_id').addEventListener('change', checkAvailability);
document.getElementById('appointment_date').addEventListener('change', checkAvailability);
document.getElementById('appointment_time').addEventListener('change', checkAvailability);

function checkAvailability() {
    const doctorId = document.getElementById('doctor_id').value;
    const appointmentDate = document.getElementById('appointment_date').value;
    const appointmentTime = document.getElementById('appointment_time').value;
    
    if (doctorId && appointmentDate && appointmentTime) {
        const appointmentDatetime = appointmentDate + ' ' + appointmentTime;
        
        // Gửi AJAX request để kiểm tra
        fetch('check_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'doctor_id=' + doctorId + '&appointment_datetime=' + appointmentDatetime
        })
        .then(response => response.json())
        .then(data => {
            if (!data.available) {
                alert('Bác sĩ đã có lịch khám vào thời gian này! Vui lòng chọn thời gian khác.');
                document.getElementById('appointment_time').value = '';
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?> 