<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID bác sĩ từ URL
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($doctor_id <= 0) {
    header('Location: manage_doctors.php');
    exit();
}

// Lấy thông tin bác sĩ
$sql = "SELECT d.*, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.address
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: manage_doctors.php');
    exit();
}

$doctor = $result->fetch_assoc();

// Lấy số lịch hẹn đã hoàn thành của bác sĩ
$appointments_sql = "SELECT COUNT(*) as completed_count FROM appointments WHERE doctor_id = ? AND status = 'completed'";
$appointments_stmt = $conn->prepare($appointments_sql);
$appointments_stmt->bind_param("i", $doctor_id);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();
$appointments_count = $appointments_result->fetch_assoc()['completed_count'];

// Lấy danh sách lịch hẹn đã hoàn thành
$recent_appointments_sql = "SELECT a.*, u.full_name as patient_name, u.phone as patient_phone
                           FROM appointments a
                           JOIN users u ON a.patient_id = u.id
                           WHERE a.doctor_id = ? AND a.status = 'completed'
                           ORDER BY a.appointment_date DESC
                           LIMIT 5";
$recent_appointments_stmt = $conn->prepare($recent_appointments_sql);
$recent_appointments_stmt->bind_param("i", $doctor_id);
$recent_appointments_stmt->execute();
$recent_appointments = $recent_appointments_stmt->get_result();

$page_title = "Chi tiết bác sĩ - " . htmlspecialchars($doctor['full_name']);
include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar"></div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-3">
                            <i class="fas fa-user-md me-3"></i>
                            <?php echo htmlspecialchars($doctor['full_name']); ?>
                        </h1>
                        <p class="mb-0 fs-5">
                            <i class="fas fa-stethoscope me-2"></i>
                            Chuyên khoa: <strong><?php echo htmlspecialchars($doctor['specialty']); ?></strong>
                        </p>
                    </div>
                    <a href="manage_doctors.php" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                </div>
            </div>

            <!-- Statistics Section -->
                    <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-calendar-check"></i>
                        <h4><?php echo $appointments_count; ?></h4>
                        <p>Lịch hẹn đã hoàn thành</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-clock"></i>
                        <h4><?php echo $doctor['experience_years']; ?> năm</h4>
                        <p>Kinh nghiệm</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-money-bill-wave"></i>
                        <h4><?php echo number_format($doctor['consultation_fee']); ?> VNĐ</h4>
                        <p>Phí khám bệnh</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-graduation-cap"></i>
                        <h4><?php echo htmlspecialchars($doctor['education']); ?></h4>
                        <p>Trình độ</p>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>
                    Thông tin cá nhân
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Họ và tên:</span>
                                <span class="info-value"><?php echo htmlspecialchars($doctor['full_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($doctor['email']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value"><?php echo htmlspecialchars($doctor['phone']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Giới tính:</span>
                                <span class="info-value">
                                    <?php if ($doctor['gender'] == 'male'): ?>
                                        <span class="badge bg-primary">Nam</span>
                                    <?php else: ?>
                                        <span class="badge bg-pink">Nữ</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Ngày sinh:</span>
                                <span class="info-value"><?php echo formatDate($doctor['date_of_birth']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Địa chỉ:</span>
                                <span class="info-value"><?php echo htmlspecialchars($doctor['address']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số giấy phép:</span>
                                <span class="info-value text-primary fw-bold"><?php echo htmlspecialchars($doctor['license_number']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-briefcase me-2"></i>
                    Thông tin chuyên môn
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Chuyên khoa:</span>
                                <span class="info-value text-primary fw-bold"><?php echo htmlspecialchars($doctor['specialty']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số năm kinh nghiệm:</span>
                                <span class="info-value text-success fw-bold"><?php echo $doctor['experience_years']; ?> năm</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Trình độ:</span>
                                <span class="info-value"><?php echo htmlspecialchars($doctor['education']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phí khám bệnh:</span>
                                <span class="info-value text-success fw-bold fs-5"><?php echo number_format($doctor['consultation_fee']); ?> VNĐ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i>
                    Lịch sử khám bệnh gần đây
                </div>
                <div class="card-body">
                    <?php if ($recent_appointments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Bệnh nhân</th>
                                        <th>Số điện thoại</th>
                                        <th>Ngày khám</th>
                                        <th>Triệu chứng</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appointment = $recent_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($appointment['patient_phone']); ?></td>
                                        <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                        <td>
                                            <?php 
                                            $symptoms = htmlspecialchars($appointment['symptoms']);
                                            echo strlen($symptoms) > 50 ? substr($symptoms, 0, 50) . '...' : $symptoms;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="view_appointment.php?id=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye me-1"></i>
                                                Xem chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($appointments_count > 5): ?>
                        <div class="text-center mt-3">
                            <a href="manage_appointments.php?doctor_id=<?php echo $doctor_id; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>
                                Xem tất cả lịch hẹn
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h5>Chưa có lịch sử khám bệnh</h5>
                            <p>Bác sĩ này chưa có lịch hẹn nào được hoàn thành.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body text-center">
                    <a href="edit_doctor.php?id=<?php echo $doctor_id; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <a href="manage_doctors.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                    <a href="manage_appointments.php?doctor_id=<?php echo $doctor_id; ?>" class="btn btn-info">
                        <i class="fas fa-calendar-check me-2"></i>
                        Xem lịch hẹn
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
