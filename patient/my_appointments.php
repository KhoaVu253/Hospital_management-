<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Chỉ cho phép bệnh nhân truy cập
requirePatient();

include '../includes/header.php';
include '../includes/top_navbar.php';

$patientId = $_SESSION['user_id'];
$appointments = getPatientAppointments($patientId);
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-calendar-check me-2"></i>
                    Lịch khám của tôi
                </h1>
                <div>
                    <a href="book_appointment.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Đặt lịch mới
                    </a>
                </div>
            </div>

            <?php $flash = getMessage(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách lịch khám</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Bác sĩ</th>
                                    <th>Chuyên khoa</th>
                                    <th>Ngày giờ</th>
                                    <th>Triệu chứng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($appointments && $appointments->num_rows > 0): ?>
                                    <?php while ($row = $appointments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo (int)$row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                                            <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['symptoms'] ?? ''); ?></td>
                                            <td>
                                                <?php
                                                switch ($row['status']) {
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
                                                    default:
                                                        echo htmlspecialchars($row['status']);
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Bạn chưa có lịch khám nào</td>
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

<?php include '../includes/footer.php'; ?>

