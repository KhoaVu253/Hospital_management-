<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền bác sĩ
requireDoctor();

// Lấy thông tin bác sĩ từ user_id
$stmt = $conn->prepare("SELECT d.*, u.full_name, u.email, u.phone FROM doctors d 
                       JOIN users u ON d.user_id = u.id 
                       WHERE d.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'];

// Thống kê lịch khám
$stmt = $conn->prepare("SELECT COUNT(*) as total,
                               SUM(status = 'pending') as pending,
                               SUM(status = 'confirmed') as confirmed,
                               SUM(status = 'completed') as completed,
                               SUM(DATE(appointment_date) = CURDATE()) as today
                        FROM appointments WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Lấy lịch khám gần đây
$stmt = $conn->prepare("SELECT a.*, u.full_name as patient_name, u.phone as patient_phone 
                       FROM appointments a 
                       JOIN users u ON a.patient_id = u.id 
                       WHERE a.doctor_id = ? 
                       ORDER BY a.appointment_date DESC 
                       LIMIT 10");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$recent_appointments = $stmt->get_result();

// Lấy lịch khám hôm nay
$stmt = $conn->prepare("SELECT a.*, u.full_name as patient_name, u.phone as patient_phone 
                       FROM appointments a 
                       JOIN users u ON a.patient_id = u.id 
                       WHERE a.doctor_id = ? AND DATE(a.appointment_date) = CURDATE()
                       ORDER BY a.appointment_date ASC");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$today_appointments = $stmt->get_result();

include '../includes/header.php';
?>

<?php include '../includes/top_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard - Bác sĩ <?php echo htmlspecialchars($doctor['full_name']); ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="appointments.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>Xem tất cả lịch khám
                        </a>
                    </div>
                </div>
            </div>

            <!-- Thông tin cá nhân bác sĩ -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user-md me-2"></i>
                                Thông tin cá nhân
                            </h6>
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
                                </div>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <span class="info-label">Chuyên khoa:</span>
                                        <span class="info-value text-primary fw-bold"><?php echo htmlspecialchars($doctor['specialty']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Kinh nghiệm:</span>
                                        <span class="info-value"><?php echo $doctor['experience_years']; ?> năm</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Phí khám:</span>
                                        <span class="info-value text-success fw-bold"><?php echo number_format($doctor['consultation_fee']); ?> VNĐ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng số lịch khám</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Lịch chờ xác nhận</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Lịch hôm nay</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['today']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã hoàn thành</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['completed']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lịch khám hôm nay -->
            <?php if ($today_appointments->num_rows > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar-day me-2"></i>
                                Lịch khám hôm nay
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Bệnh nhân</th>
                                            <th>Số điện thoại</th>
                                            <th>Giờ khám</th>
                                            <th>Triệu chứng</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $today_appointments->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['patient_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['patient_phone']); ?></td>
                                            <td><?php echo formatTime($row['appointment_date']); ?></td>
                                            <td>
                                                <?php 
                                                $symptoms = htmlspecialchars($row['symptoms']);
                                                echo strlen($symptoms) > 50 ? substr($symptoms, 0, 50) . '...' : $symptoms;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch($row['status']) {
                                                    case 'pending':
                                                        $status_class = 'badge bg-warning';
                                                        $status_text = 'Chờ xác nhận';
                                                        break;
                                                    case 'confirmed':
                                                        $status_class = 'badge bg-info';
                                                        $status_text = 'Đã xác nhận';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'badge bg-success';
                                                        $status_text = 'Hoàn thành';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'badge bg-danger';
                                                        $status_text = 'Đã hủy';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($row['status'] == 'confirmed'): ?>
                                                <a href="prescriptions.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-pills"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lịch khám gần đây -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar-check me-2"></i>
                                Lịch khám gần đây
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Bệnh nhân</th>
                                            <th>Ngày khám</th>
                                            <th>Triệu chứng</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($recent_appointments->num_rows > 0):
                                            while($row = $recent_appointments->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['patient_name']); ?></strong></td>
                                            <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                            <td>
                                                <?php 
                                                $symptoms = htmlspecialchars($row['symptoms']);
                                                echo strlen($symptoms) > 50 ? substr($symptoms, 0, 50) . '...' : $symptoms;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch($row['status']) {
                                                    case 'pending':
                                                        $status_class = 'badge bg-warning';
                                                        $status_text = 'Chờ xác nhận';
                                                        break;
                                                    case 'confirmed':
                                                        $status_class = 'badge bg-info';
                                                        $status_text = 'Đã xác nhận';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'badge bg-success';
                                                        $status_text = 'Hoàn thành';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'badge bg-danger';
                                                        $status_text = 'Đã hủy';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($row['status'] == 'confirmed'): ?>
                                                <a href="prescriptions.php?appointment_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-pills"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">Chưa có lịch khám nào</p>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
