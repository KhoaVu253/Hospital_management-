<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireAdmin();

$patientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'patient' LIMIT 1");
$stmt->bind_param('i', $patientId);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

// Lấy lịch sử khám bệnh của bệnh nhân
$appointments_sql = "SELECT a.*, d.specialty, u.full_name AS doctor_name
                     FROM appointments a
                     JOIN doctors d ON a.doctor_id = d.id
                     JOIN users u ON d.user_id = u.id
                     WHERE a.patient_id = ? AND a.status = 'completed'
                     ORDER BY a.appointment_date DESC";
$appointments_stmt = $conn->prepare($appointments_sql);
$appointments_stmt->bind_param('i', $patientId);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">\n    <div class="row">\n        <!-- Main content -->\n        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-user me-2"></i>
                    Hồ sơ Bệnh nhân
                </h1>
                <a href="manage_patients.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>

            <?php if (!$patient): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Không tìm thấy bệnh nhân.
                </div>
            <?php else: ?>
                <!-- Thông tin cơ bản -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Họ và tên</strong></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($patient['full_name']); ?>" disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Giới tính</strong></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($patient['gender']); ?>" disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Ngày sinh</strong></label>
                                <input class="form-control" value="<?php echo $patient['date_of_birth'] ? formatDate($patient['date_of_birth']) : '-'; ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Email</strong></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($patient['email']); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Số điện thoại</strong></label>
                                <input class="form-control" value="<?php echo htmlspecialchars($patient['phone']); ?>" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><strong>Địa chỉ</strong></label>
                                <textarea class="form-control" rows="2" disabled><?php echo htmlspecialchars($patient['address']); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Ngày tạo tài khoản</strong></label>
                                <input class="form-control" value="<?php echo formatDateTime($patient['created_at']); ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử khám bệnh -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử khám bệnh</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($appointments_result && $appointments_result->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                    <?php
                                    // Lấy danh sách thuốc được kê
                                    $prescriptions_sql = "SELECT p.*, m.name as medicine_name, m.price, m.unit 
                                                         FROM prescriptions p 
                                                         JOIN medicines m ON p.medicine_id = m.id 
                                                         WHERE p.appointment_id = ? 
                                                         ORDER BY p.created_at";
                                    $prescriptions_stmt = $conn->prepare($prescriptions_sql);
                                    $prescriptions_stmt->bind_param('i', $appointment['id']);
                                    $prescriptions_stmt->execute();
                                    $prescriptions_result = $prescriptions_stmt->get_result();
                                    
                                    $totalMedicineCost = 0;
                                    $medicineList = [];
                                    if ($prescriptions_result && $prescriptions_result->num_rows > 0) {
                                        while ($med = $prescriptions_result->fetch_assoc()) {
                                            $totalMedicineCost += $med['price'];
                                            $medicineList[] = $med;
                                        }
                                    }
                                    
                                    $modalId = 'patient_rx_' . $appointment['id'];
                                    ?>
                                    
                                    <div class="col-12 mb-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-info text-white">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-calendar-check me-2"></i>
                                                            Lịch khám #<?php echo $appointment['id']; ?>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 text-md-end">
                                                        <span class="badge bg-light text-dark">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo formatDateTime($appointment['appointment_date']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Thông tin bác sĩ -->
                                                    <div class="col-md-4 mb-2">
                                                        <small class="text-muted">Bác sĩ khám</small>
                                                        <p class="mb-1 fw-bold"><?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                                                        <small class="text-muted"><?php echo htmlspecialchars($appointment['specialty']); ?></small>
                                                    </div>
                                                    
                                                    <!-- Chẩn đoán -->
                                                    <div class="col-md-4 mb-2">
                                                        <small class="text-muted">Chẩn đoán</small>
                                                        <?php if (!empty($appointment['diagnosis'])): ?>
                                                            <p class="mb-1 fw-bold text-success">Đã có</p>
                                                        <?php else: ?>
                                                            <p class="mb-1 text-muted">Chưa có</p>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Đơn thuốc -->
                                                    <div class="col-md-4 mb-2">
                                                        <small class="text-muted">Đơn thuốc</small>
                                                        <?php if (!empty($medicineList)): ?>
                                                            <p class="mb-1 fw-bold text-info"><?php echo count($medicineList); ?> loại thuốc</p>
                                                            <small class="text-muted">Tổng: <?php echo formatCurrency($totalMedicineCost); ?></small>
                                                        <?php else: ?>
                                                            <p class="mb-1 text-muted">Chưa có</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Nút xem chi tiết -->
                                                <div class="text-center mt-2">
                                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal chi tiết -->
                                    <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-file-medical me-2"></i>
                                                        Chi tiết lịch khám #<?php echo $appointment['id']; ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <!-- Thông tin cơ bản -->
                                                        <div class="col-md-6">
                                                            <div class="card mb-3">
                                                                <div class="card-header">
                                                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <table class="table table-borderless">
                                                                        <tr>
                                                                            <td><strong>Ngày khám:</strong></td>
                                                                            <td><?php echo formatDateTime($appointment['appointment_date']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Bác sĩ:</strong></td>
                                                                            <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Chuyên khoa:</strong></td>
                                                                            <td><?php echo htmlspecialchars($appointment['specialty']); ?></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Triệu chứng -->
                                                        <div class="col-md-6">
                                                            <div class="card mb-3">
                                                                <div class="card-header">
                                                                    <h6 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Triệu chứng</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <?php if (!empty($appointment['symptoms'])): ?>
                                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($appointment['symptoms'])); ?></p>
                                                                    <?php else: ?>
                                                                        <p class="text-muted mb-0">Không có triệu chứng được ghi nhận</p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Chẩn đoán -->
                                                    <?php if (!empty($appointment['diagnosis'])): ?>
                                                    <div class="card mb-3">
                                                        <div class="card-header bg-success text-white">
                                                            <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Chẩn đoán</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($appointment['diagnosis'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Đơn thuốc -->
                                                    <?php if (!empty($medicineList)): ?>
                                                    <div class="card mb-3">
                                                        <div class="card-header bg-info text-white">
                                                            <h6 class="mb-0"><i class="fas fa-pills me-2"></i>Đơn thuốc</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <?php if (!empty($appointment['prescription'])): ?>
                                                                <div class="mb-3">
                                                                    <h6>Ghi chú đơn thuốc:</h6>
                                                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($appointment['prescription'])); ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <h6>Danh sách thuốc:</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>STT</th>
                                                                            <th>Tên thuốc</th>
                                                                            <th>Liều lượng</th>
                                                                            <th>Thời gian</th>
                                                                            <th>Hướng dẫn</th>
                                                                            <th>Đơn giá</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($medicineList as $index => $medicine): ?>
                                                                            <tr>
                                                                                <td><?php echo $index + 1; ?></td>
                                                                                <td>
                                                                                    <strong><?php echo htmlspecialchars($medicine['medicine_name']); ?></strong>
                                                                                    <br><small class="text-muted"><?php echo htmlspecialchars($medicine['unit']); ?></small>
                                                                                </td>
                                                                                <td><?php echo htmlspecialchars($medicine['dosage']); ?></td>
                                                                                <td><?php echo htmlspecialchars($medicine['duration'] ?? 'Không xác định'); ?></td>
                                                                                <td><?php echo nl2br(htmlspecialchars($medicine['instructions'] ?? 'Không có')); ?></td>
                                                                                <td><?php echo formatCurrency($medicine['price']); ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr class="table-info">
                                                                            <td colspan="5" class="text-end"><strong>Tổng tiền thuốc:</strong></td>
                                                                            <td><strong><?php echo formatCurrency($totalMedicineCost); ?></strong></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Ghi chú -->
                                                    <?php if (!empty($appointment['notes'])): ?>
                                                    <div class="card mb-3">
                                                        <div class="card-header">
                                                            <h6 class="mb-0"><i class="fas fa-clipboard me-2"></i>Ghi chú</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-3"></i>
                                <h6 class="text-muted">Chưa có lịch khám hoàn thành</h6>
                                <p class="text-muted">Bệnh nhân này chưa có lịch khám nào được hoàn thành.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


    <?php if (!$patient): ?>
        <div class="alert alert-warning">Không tìm thấy bệnh nhân.</div>
    <?php else: ?>
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Hồ sơ bệnh nhân</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Họ và tên</label>
                        <input class="form-control" value="<?php echo htmlspecialchars($patient['full_name']); ?>" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giới tính</label>
                        <input class="form-control" value="<?php echo htmlspecialchars($patient['gender']); ?>" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ngày sinh</label>
                        <input class="form-control" value="<?php echo $patient['date_of_birth'] ? formatDate($patient['date_of_birth']) : '-'; ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" value="<?php echo htmlspecialchars($patient['email']); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input class="form-control" value="<?php echo htmlspecialchars($patient['phone']); ?>" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" rows="2" disabled><?php echo htmlspecialchars($patient['address']); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ngày tạo</label>
                        <input class="form-control" value="<?php echo formatDateTime($patient['created_at']); ?>" disabled>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>

