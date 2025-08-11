<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

// Thống kê tổng quan
$total_patients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'patient'")->fetch_assoc()['count'];
$total_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'")->fetch_assoc()['count'];
$today_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetch_assoc()['count'];
$monthly_revenue = $conn->query("SELECT SUM(total_amount) as total FROM bills WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'paid'")->fetch_assoc()['total'] ?? 0;

// Lịch khám gần đây
$recent_appointments = $conn->query("SELECT a.*, p.full_name as patient_name, d.specialty, doc.full_name as doctor_name 
                                    FROM appointments a 
                                    JOIN users p ON a.patient_id = p.id 
                                    JOIN doctors d ON a.doctor_id = d.id 
                                    JOIN users doc ON d.user_id = doc.id 
                                    ORDER BY a.appointment_date DESC 
                                    LIMIT 8");

// Thống kê theo chuyên khoa
$specialty_stats = $conn->query("SELECT d.specialty, COUNT(*) as count 
                                FROM appointments a 
                                JOIN doctors d ON a.doctor_id = d.id 
                                WHERE MONTH(a.appointment_date) = MONTH(CURDATE()) 
                                GROUP BY d.specialty 
                                ORDER BY count DESC 
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
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="mb-2">
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                            Dashboard Quản lý
                        </h1>
                        <p class="mb-0">
                            <i class="fas fa-chart-line me-2 text-muted"></i>
                            Tổng quan hệ thống bệnh viện
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Xuất báo cáo
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-2"></i>Thêm mới
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="add_patient.php">
                                    <i class="fas fa-user me-2"></i>Thêm bệnh nhân
                                </a></li>
                                <li><a class="dropdown-item" href="add_doctor.php">
                                    <i class="fas fa-user-md me-2"></i>Thêm bác sĩ
                                </a></li>
                                <li><a class="dropdown-item" href="add_medicine.php">
                                    <i class="fas fa-pills me-2"></i>Thêm thuốc
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-5">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-users text-primary"></i>
                        <h4 class="text-primary"><?php echo number_format($total_patients); ?></h4>
                        <p class="text-muted mb-0">Tổng số bệnh nhân</p>
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            +12% so với tháng trước
                        </small>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-user-md text-success"></i>
                        <h4 class="text-success"><?php echo number_format($total_doctors); ?></h4>
                        <p class="text-muted mb-0">Tổng số bác sĩ</p>
                        <small class="text-info">
                            <i class="fas fa-check-circle me-1"></i>
                            Đang hoạt động
                        </small>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-calendar-day text-warning"></i>
                        <h4 class="text-warning"><?php echo number_format($today_appointments); ?></h4>
                        <p class="text-muted mb-0">Lịch khám hôm nay</p>
                        <small class="text-primary">
                            <i class="fas fa-clock me-1"></i>
                            Cập nhật realtime
                        </small>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-dollar-sign text-info"></i>
                        <h4 class="text-info"><?php echo formatCurrency($monthly_revenue); ?></h4>
                        <p class="text-muted mb-0">Doanh thu tháng</p>
                        <small class="text-success">
                            <i class="fas fa-trending-up me-1"></i>
                            +8.5% so với tháng trước
                        </small>
                    </div>
                </div>
            </div>

            <!-- Lịch khám gần đây và Thống kê -->
            <div class="row">
                <!-- Lịch khám gần đây -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-check me-2 text-primary"></i>
                                Lịch khám gần đây
                            </h5>
                            <span class="badge bg-primary"><?php echo $recent_appointments->num_rows; ?> lịch khám</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th class="border-0">
                                                <i class="fas fa-user me-2"></i>Bệnh nhân
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-user-md me-2"></i>Bác sĩ
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-calendar me-2"></i>Ngày khám
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-info-circle me-2"></i>Trạng thái
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-cogs me-2"></i>Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recent_appointments->num_rows > 0): ?>
                                            <?php while ($row = $recent_appointments->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($row['patient_name']); ?></h6>
                                                            <small class="text-muted">Bệnh nhân</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-user-md text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($row['doctor_name']); ?></h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($row['specialty']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="fw-bold"><?php echo date('d/m/Y', strtotime($row['appointment_date'])); ?></span>
                                                        <br>
                                                        <small class="text-muted"><?php echo date('H:i', strtotime($row['appointment_date'])); ?></small>
                                                    </div>
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
                                                    <div class="btn-group" role="group">
                                                        <a href="view_appointment.php?id=<?php echo $row['id']; ?>"
                                                           class="btn btn-sm btn-outline-info"
                                                           data-bs-toggle="tooltip"
                                                           title="Xem chi tiết">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_appointment.php?id=<?php echo $row['id']; ?>"
                                                           class="btn btn-sm btn-outline-warning"
                                                           data-bs-toggle="tooltip"
                                                           title="Chỉnh sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="empty-state">
                                                        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 3rem;"></i>
                                                        <h5 class="text-muted">Chưa có lịch khám</h5>
                                                        <p class="text-muted mb-3">Hệ thống chưa có lịch khám nào được đặt.</p>
                                                        <a href="manage_appointments.php" class="btn btn-primary">
                                                            <i class="fas fa-plus me-2"></i>Tạo lịch khám mới
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
                                <a href="manage_appointments.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>Xem tất cả lịch khám
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Thống kê chuyên khoa -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2 text-primary"></i>
                                Thống kê chuyên khoa
                            </h5>
                            <span class="badge bg-info">Tháng này</span>
                        </div>
                        <div class="card-body">
                            <?php if ($specialty_stats->num_rows > 0): ?>
                                <div class="specialty-stats">
                                    <?php
                                    $colors = ['primary', 'success', 'warning', 'info', 'danger'];
                                    $index = 0;
                                    while ($specialty = $specialty_stats->fetch_assoc()):
                                        $color = $colors[$index % count($colors)];
                                        $index++;
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded border-start border-4 border-<?php echo $color; ?>" style="background: var(--gray-50);">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-<?php echo $color; ?> rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <i class="fas fa-stethoscope text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($specialty['specialty']); ?></h6>
                                                <small class="text-muted">Chuyên khoa</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="mb-0 text-<?php echo $color; ?>"><?php echo $specialty['count']; ?></h5>
                                            <small class="text-muted">lịch khám</small>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-chart-bar"></i>
                                    <h5>Chưa có dữ liệu</h5>
                                    <p>Chưa có thống kê theo chuyên khoa.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2 text-warning"></i>
                                Thao tác nhanh
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-3">
                                <a href="manage_appointments.php" class="btn btn-outline-primary d-flex align-items-center justify-content-start">
                                    <i class="fas fa-calendar-check me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Quản lý lịch khám</div>
                                        <small class="text-muted">Xem và quản lý tất cả lịch khám</small>
                                    </div>
                                </a>
                                <a href="manage_doctors.php" class="btn btn-outline-success d-flex align-items-center justify-content-start">
                                    <i class="fas fa-user-md me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Quản lý bác sĩ</div>
                                        <small class="text-muted">Thêm, sửa thông tin bác sĩ</small>
                                    </div>
                                </a>
                                <a href="manage_patients.php" class="btn btn-outline-info d-flex align-items-center justify-content-start">
                                    <i class="fas fa-users me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Quản lý bệnh nhân</div>
                                        <small class="text-muted">Quản lý hồ sơ bệnh nhân</small>
                                    </div>
                                </a>
                                <a href="manage_bills.php" class="btn btn-outline-warning d-flex align-items-center justify-content-start">
                                    <i class="fas fa-file-invoice-dollar me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-bold">Quản lý hóa đơn</div>
                                        <small class="text-muted">Theo dõi thanh toán</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ thống kê -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-chart-area me-2"></i>
                                Thống kê lịch khám theo tháng
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="appointmentChart" width="100%" height="40"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card, .stats-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
});
</script>
<script>
// Biểu đồ lịch khám theo tháng
const appointmentCtx = document.getElementById('appointmentChart').getContext('2d');
const appointmentChart = new Chart(appointmentCtx, {
    type: 'line',
    data: {
        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        datasets: [{
            label: 'Số lịch khám',
            data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 12],
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Thống kê lịch khám theo tháng'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?> 