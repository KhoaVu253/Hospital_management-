<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$doctorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin bác sĩ
$stmt = $conn->prepare("SELECT d.id, u.full_name, d.specialty FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ? LIMIT 1");
$stmt->bind_param('i', $doctorId);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

// Lấy lịch khám
$appointments = null;
if ($doctor) {
    $stmt = $conn->prepare("SELECT a.*, u.full_name AS patient_name 
                             FROM appointments a 
                             JOIN users u ON a.patient_id = u.id 
                             WHERE a.doctor_id = ? 
                             ORDER BY a.appointment_date DESC");
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $appointments = $stmt->get_result();
}

include '../includes/header.php';
?>
<div class="container py-4">
    <a href="manage_doctors.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>

    <?php if (!$doctor): ?>
        <div class="alert alert-warning">Không tìm thấy bác sĩ.</div>
    <?php else: ?>
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Lịch khám của: <?php echo htmlspecialchars($doctor['full_name']); ?> (<?php echo htmlspecialchars($doctor['specialty']); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Bệnh nhân</th>
                                <th>Ngày giờ</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($appointments && $appointments->num_rows > 0): ?>
                                <?php while ($row = $appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                        <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                        <td>
                                            <?php
                                            $statusMap = [
                                                'pending' => 'badge bg-warning',
                                                'confirmed' => 'badge bg-success',
                                                'completed' => 'badge bg-primary',
                                                'cancelled' => 'badge bg-secondary'
                                            ];
                                            $class = $statusMap[$row['status']] ?? 'badge bg-secondary';
                                            ?>
                                            <span class="<?php echo $class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">Chưa có lịch khám</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>

