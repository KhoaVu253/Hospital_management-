<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền bệnh nhân
requirePatient();

// Lấy thông tin bệnh nhân
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// Thống kê tổng quan
$total_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = " . $_SESSION['user_id'])->fetch_assoc()['count'];
$completed_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = " . $_SESSION['user_id'] . " AND status = 'completed'")->fetch_assoc()['count'];
$pending_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = " . $_SESSION['user_id'] . " AND status = 'pending'")->fetch_assoc()['count'];
$total_paid = $conn->query("SELECT SUM(b.total_amount) as total FROM bills b 
                           JOIN appointments a ON b.appointment_id = a.id 
                           WHERE a.patient_id = " . $_SESSION['user_id'] . " AND b.status = 'paid'")->fetch_assoc()['total'] ?? 0;

// Lịch khám gần đây
$recent_appointments = $conn->query("SELECT a.*, d.specialty, u.full_name as doctor_name 
                                    FROM appointments a 
                                    JOIN doctors d ON a.doctor_id = d.id 
                                    JOIN users u ON d.user_id = u.id 
                                    WHERE a.patient_id = " . $_SESSION['user_id'] . " 
                                    ORDER BY a.appointment_date DESC 
                                    LIMIT 5");

// Hóa đơn gần đây
$recent_bills = $conn->query("SELECT b.*, a.appointment_date, u.full_name as doctor_name 
                              FROM bills b 
                              JOIN appointments a ON b.appointment_id = a.id 
                              JOIN doctors d ON a.doctor_id = d.id 
                              JOIN users u ON d.user_id = u.id 
                              WHERE a.patient_id = " . $_SESSION['user_id'] . " 
                              ORDER BY b.created_at DESC 
                              LIMIT 5");

include '../includes/header.php';
?>

<?php include '../includes/top_navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="fas fa-user me-3"></i>
                            Dashboard - <?php echo htmlspecialchars($patient['full_name']); ?>
                        </h1>
                        <p class="mb-0 fs-5">
                            <i class="fas fa-hospital me-2"></i>
                            DUCKHOA Hospital - Chào mừng bạn trở lại!
                        </p>
                    </div>
                    <div class="btn-toolbar">
                        <a href="book_appointment.php" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám
                        </a>
                    </div>
                </div>
            </div>

            <!-- Thông tin cá nhân -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-user me-2"></i>
                                Thông tin cá nhân
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <span class="info-label">Họ tên:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($patient['full_name']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Email:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($patient['email']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Số điện thoại:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($patient['phone']); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <span class="info-label">Tên đăng nhập:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($patient['username']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Ngày tham gia:</span>
                                        <span class="info-value"><?php echo formatDate($patient['created_at']); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Trạng thái:</span>
                                        <span class="info-value">
                                            <span class="badge bg-success">Hoạt động</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="profile.php" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i>Cập nhật thông tin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-calendar"></i>
                        <h4><?php echo $total_appointments; ?></h4>
                        <p>Tổng số lịch khám</p>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-check-circle"></i>
                        <h4><?php echo $completed_appointments; ?></h4>
                        <p>Lịch khám đã hoàn thành</p>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-clock"></i>
                        <h4><?php echo $pending_appointments; ?></h4>
                        <p>Lịch khám chờ xác nhận</p>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-dollar-sign"></i>
                        <h4><?php echo formatCurrency($total_paid); ?></h4>
                        <p>Tổng chi phí đã thanh toán</p>
                    </div>
                </div>
            </div>

            <!-- Lịch khám và Hóa đơn -->
            <div class="row">
                <!-- Lịch khám gần đây -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calendar-check me-2"></i>
                                Lịch khám gần đây
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Bác sĩ</th>
                                            <th>Chuyên khoa</th>
                                            <th>Ngày khám</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_appointments->num_rows > 0): ?>
                                            <?php while ($row = $recent_appointments->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['doctor_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                                                <td><?php echo formatDateTime($row['appointment_date']); ?></td>
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
                                                    <?php if ($row['status'] == 'pending'): ?>
                                                        <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Bạn có chắc chắn muốn hủy lịch khám này?')">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <div class="empty-state">
                                                        <i class="fas fa-calendar-times"></i>
                                                        <h5>Chưa có lịch khám</h5>
                                                        <p>Bạn chưa có lịch khám nào</p>
                                                        <a href="book_appointment.php" class="btn btn-primary">
                                                            <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám ngay
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($recent_appointments->num_rows > 0): ?>
                            <div class="text-center mt-3">
                                <a href="my_appointments.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>Xem tất cả lịch khám
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Hóa đơn gần đây -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Hóa đơn gần đây
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if ($recent_bills->num_rows > 0): ?>
                                <div class="bill-list">
                                    <?php while ($row = $recent_bills->fetch_assoc()): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(102, 126, 234, 0.05);">
                                        <div>
                                            <strong>#<?php echo $row['id']; ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($row['doctor_name']); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success"><?php echo formatCurrency($row['total_amount']); ?></div>
                                            <span class="badge <?php echo $row['status'] == 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo $row['status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice"></i>
                                    <h5>Chưa có hóa đơn</h5>
                                    <p>Bạn chưa có hóa đơn nào.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow mt-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-bolt me-2"></i>
                                Thao tác nhanh
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="book_appointment.php" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám
                                </a>
                                <a href="my_appointments.php" class="btn btn-outline-success">
                                    <i class="fas fa-calendar-check me-2"></i>Lịch khám của tôi
                                </a>
                                <a href="medical_history.php" class="btn btn-outline-info">
                                    <i class="fas fa-history me-2"></i>Lịch sử khám bệnh
                                </a>
                                <a href="my_bills.php" class="btn btn-outline-warning">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>Hóa đơn của tôi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 