<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Chỉ bệnh nhân được truy cập
requirePatient();

include '../includes/header.php';
include '../includes/top_navbar.php';

$patientId = $_SESSION['user_id'];

$sql = "SELECT a.*, d.specialty, u.full_name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE a.patient_id = ? AND a.status = 'completed'
        ORDER BY a.appointment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patientId);
$stmt->execute();
$history = $stmt->get_result();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-history me-2"></i>
                    Lịch sử khám bệnh
                </h1>
            </div>

            <?php if ($history && $history->num_rows > 0): ?>
                <div class="row">
                    <?php while ($row = $history->fetch_assoc()): ?>
                        <?php
                        // Lấy danh sách thuốc được kê
                        $prescriptionsStmt = $conn->prepare("SELECT p.*, m.name as medicine_name, m.price, m.unit 
                                                           FROM prescriptions p 
                                                           JOIN medicines m ON p.medicine_id = m.id 
                                                           WHERE p.appointment_id = ? 
                                                           ORDER BY p.created_at");
                        $prescriptionsStmt->bind_param('i', $row['id']);
                        $prescriptionsStmt->execute();
                        $prescriptions = $prescriptionsStmt->get_result();
                        
                        $modalId = 'rx_' . $row['id'];
                        $totalMedicineCost = 0;
                        $medicineList = [];
                        if ($prescriptions && $prescriptions->num_rows > 0) {
                            while ($med = $prescriptions->fetch_assoc()) {
                                $totalMedicineCost += $med['price'];
                                $medicineList[] = $med;
                            }
                        }
                        ?>
                        
                        <div class="col-12 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5 class="mb-0">
                                                <i class="fas fa-calendar-check me-2"></i>
                                                Lịch khám #<?php echo (int)$row['id']; ?>
                                            </h5>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo formatDateTime($row['appointment_date']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Thông tin bác sĩ -->
                                        <div class="col-md-4 mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle p-3 me-3">
                                                    <i class="fas fa-user-md text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Bác sĩ khám</h6>
                                                    <p class="mb-1 fw-bold"><?php echo htmlspecialchars($row['doctor_name']); ?></p>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['specialty']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Chẩn đoán -->
                                        <div class="col-md-4 mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success rounded-circle p-3 me-3">
                                                    <i class="fas fa-stethoscope text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Chẩn đoán</h6>
                                                    <?php if (!empty($row['diagnosis'])): ?>
                                                        <p class="mb-1 fw-bold text-success">Đã có</p>
                                                        <small class="text-muted">Bác sĩ đã chẩn đoán</small>
                                                    <?php else: ?>
                                                        <p class="mb-1 text-muted">Chưa có</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Đơn thuốc -->
                                        <div class="col-md-4 mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info rounded-circle p-3 me-3">
                                                    <i class="fas fa-pills text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Đơn thuốc</h6>
                                                    <?php if (!empty($medicineList)): ?>
                                                        <p class="mb-1 fw-bold text-info"><?php echo count($medicineList); ?> loại thuốc</p>
                                                        <small class="text-muted">Tổng: <?php echo formatCurrency($totalMedicineCost); ?></small>
                                                    <?php else: ?>
                                                        <p class="mb-1 text-muted">Chưa có</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Nút xem chi tiết -->
                                    <div class="text-center mt-3">
                                        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                                            <i class="fas fa-eye me-2"></i>Xem chi tiết
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal chi tiết -->
                        <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-file-medical me-2"></i>
                                            Chi tiết lịch khám #<?php echo (int)$row['id']; ?>
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
                                                                <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Bác sĩ:</strong></td>
                                                                <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Chuyên khoa:</strong></td>
                                                                <td><?php echo htmlspecialchars($row['specialty']); ?></td>
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
                                                        <?php if (!empty($row['symptoms'])): ?>
                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['symptoms'])); ?></p>
                                                        <?php else: ?>
                                                            <p class="text-muted mb-0">Không có triệu chứng được ghi nhận</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Chẩn đoán -->
                                        <?php if (!empty($row['diagnosis'])): ?>
                                        <div class="card mb-3">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Chẩn đoán</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['diagnosis'])); ?></p>
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
                                                <?php if (!empty($row['prescription'])): ?>
                                                    <div class="mb-3">
                                                        <h6>Ghi chú đơn thuốc:</h6>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['prescription'])); ?></p>
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
                                        <?php if (!empty($row['notes'])): ?>
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="fas fa-clipboard me-2"></i>Ghi chú</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['notes'])); ?></p>
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
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có lịch khám hoàn thành</h5>
                        <p class="text-muted">Bạn chưa có lịch khám nào được hoàn thành.</p>
                        <a href="my_appointments.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>Xem lịch khám
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Các lần khám đã hoàn thành</h5>
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
                                    <th>Chẩn đoán</th>
                                    <th>Đơn thuốc</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($history && $history->num_rows > 0): ?>
                                    <?php while ($row = $history->fetch_assoc()): ?>
                                        <?php
                                        $prescriptionsStmt = $conn->prepare("SELECT p.*, m.name FROM prescriptions p JOIN medicines m ON p.medicine_id = m.id WHERE p.appointment_id = ?");
                                        $prescriptionsStmt->bind_param('i', $row['id']);
                                        $prescriptionsStmt->execute();
                                        $prescriptions = $prescriptionsStmt->get_result();
                                        $modalId = 'rx_' . $row['id'];
                                        ?>
                                        <tr>
                                            <td><?php echo (int)$row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                                            <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($row['diagnosis'] ?? '')); ?></td>
                                            <td>
                                                <?php if ($prescriptions && $prescriptions->num_rows > 0): ?>
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#<?php echo $modalId; ?>">
                                                        <i class="fas fa-prescription-bottle-alt me-1"></i> Xem đơn thuốc
                                                    </button>

                                                    <!-- Modal đơn thuốc -->
                                                    <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">
                                                                        <i class="fas fa-prescription me-2"></i>Đơn thuốc - Lịch #<?php echo (int)$row['id']; ?>
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="table-responsive">
                                                                        <table class="table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Thuốc</th>
                                                                                    <th>Liều dùng</th>
                                                                                    <th>Thời gian</th>
                                                                                    <th>Hướng dẫn</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php while ($rx = $prescriptions->fetch_assoc()): ?>
                                                                                    <tr>
                                                                                        <td><?php echo htmlspecialchars($rx['name']); ?></td>
                                                                                        <td><?php echo htmlspecialchars($rx['dosage']); ?></td>
                                                                                        <td><?php echo htmlspecialchars($rx['duration'] ?? ''); ?></td>
                                                                                        <td><?php echo nl2br(htmlspecialchars($rx['instructions'] ?? '')); ?></td>
                                                                                    </tr>
                                                                                <?php endwhile; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Không có</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Chưa có lịch khám hoàn thành.
                                            <a href="my_appointments.php" class="ms-2">Xem lịch khám</a>
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

<?php include '../includes/footer.php'; ?>

