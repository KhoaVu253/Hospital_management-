<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID lịch hẹn từ URL
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($appointment_id <= 0) {
    header('Location: manage_appointments.php');
    exit();
}

// Lấy thông tin lịch hẹn
$sql = "SELECT a.*, 
               u1.full_name as patient_name, u1.email as patient_email, u1.phone as patient_phone,
               u2.full_name as doctor_name, u2.email as doctor_email, u2.phone as doctor_phone,
               d.specialty, d.consultation_fee
        FROM appointments a 
        JOIN users u1 ON a.patient_id = u1.id 
        JOIN doctors d ON a.doctor_id = d.id 
        JOIN users u2 ON d.user_id = u2.id 
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: manage_appointments.php');
    exit();
}

$appointment = $result->fetch_assoc();

// Lấy thông tin hóa đơn nếu có
$bill_sql = "SELECT * FROM bills WHERE appointment_id = ?";
$bill_stmt = $conn->prepare($bill_sql);
$bill_stmt->bind_param("i", $appointment_id);
$bill_stmt->execute();
$bill_result = $bill_stmt->get_result();
$bill = $bill_result->fetch_assoc();

$page_title = "Chi tiết lịch hẹn #" . $appointment_id;
include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->


        <!-- Main Content -->
        <div class="col-12 main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-3">
                            <i class="fas fa-calendar-check me-3"></i>
                            Chi tiết lịch hẹn #<?php echo $appointment_id; ?>
                        </h1>
                        <p class="mb-0 fs-5">
                            <i class="fas fa-user me-2"></i>
                            Bệnh nhân: <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                        </p>
                    </div>
                    <a href="manage_appointments.php" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                </div>
            </div>

            <!-- Appointment Status Cards -->
            <div class="row mb-5">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-calendar-alt"></i>
                        <h4><?php echo formatDate($appointment['appointment_date']); ?></h4>
                        <p>Ngày khám</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-clock"></i>
                        <h4><?php echo formatTime($appointment['appointment_date']); ?></h4>
                        <p>Giờ khám</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-user-md"></i>
                        <h4><?php echo htmlspecialchars($appointment['doctor_name']); ?></h4>
                        <p>Bác sĩ</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-stethoscope"></i>
                        <h4><?php echo htmlspecialchars($appointment['specialty']); ?></h4>
                        <p>Chuyên khoa</p>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>
                    Thông tin bệnh nhân
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Họ và tên:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['patient_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['patient_email']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['patient_phone']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Triệu chứng:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['symptoms']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-md me-2"></i>
                    Thông tin bác sĩ
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Họ và tên:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['doctor_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['doctor_email']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value"><?php echo htmlspecialchars($appointment['doctor_phone']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phí khám:</span>
                                <span class="info-value text-success fw-bold"><?php echo number_format($appointment['consultation_fee']); ?> VNĐ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2"></i>
                    Chi tiết lịch hẹn
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Trạng thái:</span>
                                <span class="info-value">
                                    <?php
                                    switch($appointment['status']) {
                                        case 'pending':
                                            echo "<span class='badge bg-warning'>Chờ xác nhận</span>";
                                            break;
                                        case 'confirmed':
                                            echo "<span class='badge bg-success'>Đã xác nhận</span>";
                                            break;
                                        case 'completed':
                                            echo "<span class='badge bg-info'>Hoàn thành</span>";
                                            break;
                                        case 'cancelled':
                                            echo "<span class='badge bg-danger'>Đã hủy</span>";
                                            break;
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ngày tạo:</span>
                                <span class="info-value"><?php echo formatDateTime($appointment['created_at']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Ghi chú:</span>
                                <span class="info-value">
                                    <?php echo !empty($appointment['notes']) ? htmlspecialchars($appointment['notes']) : '<em class="text-muted">Không có</em>'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($appointment['diagnosis']) || !empty($appointment['prescription'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-notes-medical me-2"></i>
                    Thông tin y tế
                </div>
                <div class="card-body">
                    <?php if (!empty($appointment['diagnosis'])): ?>
                    <div class="mb-3">
                        <h6 class="text-primary fw-bold mb-2">
                            <i class="fas fa-stethoscope me-2"></i>
                            Chẩn đoán:
                        </h6>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($appointment['diagnosis'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($appointment['prescription'])): ?>
                    <div class="mb-0">
                        <h6 class="text-primary fw-bold mb-2">
                            <i class="fas fa-pills me-2"></i>
                            Đơn thuốc:
                        </h6>
                        <div class="prescription-details">
                            <?php echo nl2br(htmlspecialchars($appointment['prescription'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($bill): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Thông tin hóa đơn
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Mã hóa đơn:</span>
                                <span class="info-value">#<?php echo $bill['id']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tổng tiền:</span>
                                <span class="info-value text-success fw-bold fs-5"><?php echo number_format($bill['total_amount']); ?> VNĐ</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Trạng thái:</span>
                                <span class="info-value">
                                    <?php if ($bill['unpaid']): ?>
                                        <span class='badge bg-warning'>Chưa thanh toán</span>
                                    <?php else: ?>
                                        <span class='badge bg-success'>Đã thanh toán</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ngày tạo:</span>
                                <span class="info-value"><?php echo formatDateTime($bill['created_at']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body text-center">
                    <a href="edit_appointment.php?id=<?php echo $appointment_id; ?>" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <a href="manage_appointments.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại
                    </a>
                    <?php if ($bill): ?>
                    <a href="view_bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-info">
                        <i class="fas fa-file-invoice me-2"></i>
                        Xem hóa đơn
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
