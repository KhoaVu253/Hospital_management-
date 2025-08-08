<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền bác sĩ
requireDoctor();

// Lấy thông tin bác sĩ
$stmt = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$doctor_id = $doctor['id'];

$message = getMessage();

// Cập nhật trạng thái lịch khám
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = (int)$_POST['appointment_id'];
    $status = sanitizeInput($_POST['status']);

    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param("sii", $status, $appointment_id, $doctor_id);

    if ($stmt->execute()) {
        showMessage('Cập nhật trạng thái thành công');
    } else {
        showMessage('Có lỗi xảy ra, vui lòng thử lại', 'danger');
    }
    header('Location: appointments.php');
    exit();
}

$appointments = getDoctorAppointments($doctor_id);

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
                        <a class="nav-link active" href="appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Lịch khám
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-calendar-check me-2"></i>
                    Lịch khám
                </h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
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
                                <?php while($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['symptoms']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn btn-sm btn-success">Xác nhận</button>
                                            </form>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-danger">Hủy</button>
                                            </form>
                                        <?php elseif ($row['status'] === 'confirmed'): ?>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-primary">Hoàn thành</button>
                                            </form>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-danger">Hủy</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Không có thao tác</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if($appointments->num_rows == 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có lịch khám</td>
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
