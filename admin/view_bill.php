<?php
require_once '../includes/functions.php';
require_once '../config/db.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';
$error = '';

if (isset($_GET['id'])) {
    $bill_id = (int)$_GET['id'];
    
    // Lấy thông tin hóa đơn
    $sql = "SELECT b.*, a.appointment_date, u1.full_name as patient_name, u1.phone as patient_phone, 
                   u2.full_name as doctor_name, d.specialty
            FROM bills b
            JOIN appointments a ON b.appointment_id = a.id
            JOIN users u1 ON a.patient_id = u1.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u2 ON d.user_id = u2.id
            WHERE b.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = "Hóa đơn không tồn tại!";
    } else {
        $bill = $result->fetch_assoc();
    }
    $stmt->close();
} else {
    $error = "Không có ID hóa đơn được cung cấp!";
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row"><main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Chi tiết hóa đơn</h1>
                <a href="manage_bills.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (isset($bill)): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>
                                    Hóa đơn #BILL-<?php echo str_pad($bill['id'], 6, '0', STR_PAD_LEFT); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Thông tin bệnh nhân</h6>
                                        <p><strong>Tên:</strong> <?php echo htmlspecialchars($bill['patient_name']); ?></p>
                                        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($bill['patient_phone']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Thông tin bác sĩ</h6>
                                        <p><strong>Tên:</strong> <?php echo htmlspecialchars($bill['doctor_name']); ?></p>
                                        <p><strong>Chuyên khoa:</strong> <?php echo htmlspecialchars($bill['specialty']); ?></p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Thông tin lịch khám</h6>
                                        <p><strong>Ngày khám:</strong> <?php echo formatDateTime($bill['appointment_date']); ?></p>
                                        <p><strong>Trạng thái:</strong> 
                                            <?php
                                            switch($bill['status']) {
                                                case 'unpaid':
                                                    echo "<span class='badge bg-warning'>Chờ thanh toán</span>";
                                                    break;
                                                case 'paid':
                                                    echo "<span class='badge bg-success'>Đã thanh toán</span>";
                                                    break;
                                                case 'cancelled':
                                                    echo "<span class='badge bg-danger'>Đã hủy</span>";
                                                    break;
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Thông tin hóa đơn</h6>
                                        <p><strong>Ngày tạo:</strong> <?php echo formatDateTime($bill['created_at']); ?></p>
                                        <?php if ($bill['payment_date']): ?>
                                            <p><strong>Ngày thanh toán:</strong> <?php echo formatDateTime($bill['payment_date']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Chi tiết chi phí</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phí khám:</span>
                                    <span><?php echo formatCurrency($bill['consultation_fee']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Thuốc:</span>
                                    <span><?php echo formatCurrency($bill['medicine_fee']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Chi phí khác:</span>
                                    <span><?php echo formatCurrency($bill['other_fees']); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Tổng cộng:</span>
                                    <span class="text-primary"><?php echo formatCurrency($bill['total_amount']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="edit_bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <a href="manage_bills.php" class="btn btn-secondary w-100">
                                <i class="fas fa-list"></i> Danh sách hóa đơn
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
