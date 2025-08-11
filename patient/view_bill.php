<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

requirePatient();

$billId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($billId <= 0) {
    header('Location: my_bills.php');
    exit();
}

$sql = "SELECT b.*, a.appointment_date, u1.full_name as patient_name, u2.full_name as doctor_name
        FROM bills b
        JOIN appointments a ON b.appointment_id = a.id
        JOIN users u1 ON a.patient_id = u1.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u2 ON d.user_id = u2.id
        WHERE b.id = ? AND a.patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $billId, $_SESSION['user_id']);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();

if (!$bill) {
    showMessage('Không tìm thấy hóa đơn!', 'danger');
    header('Location: my_bills.php');
    exit();
}

include '../includes/header.php';\ninclude '../includes/top_navbar.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Hóa đơn #<?php echo $bill['id']; ?></h5>
                    <span class="badge <?php echo $bill['status']==='paid'?'bg-success':'bg-warning text-dark'; ?>">
                        <?php echo $bill['status']==='paid'?'Đã thanh toán':'Chưa thanh toán'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Ngày khám:</strong> <?php echo formatDateTime($bill['appointment_date']); ?><br>
                        <strong>Bác sĩ:</strong> <?php echo htmlspecialchars($bill['doctor_name']); ?><br>
                        <strong>Bệnh nhân:</strong> <?php echo htmlspecialchars($bill['patient_name']); ?>
                    </div>

                    <hr>
                    <h6>Chi tiết chi phí</h6>
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tiền khám</span>
                            <strong><?php echo formatCurrency($bill['consultation_fee']); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tiền thuốc</span>
                            <strong><?php echo formatCurrency($bill['medicine_fee']); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Các chi phí khác</span>
                            <strong><?php echo formatCurrency($bill['other_fees']); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Tổng cộng</span>
                            <strong><?php echo formatCurrency($bill['total_amount']); ?></strong>
                        </li>
                    </ul>

                    <?php if ($bill['status'] === 'unpaid'): ?>
                        <a href="pay_bill.php?id=<?php echo $bill['id']; ?>" class="btn btn-success" onclick="return confirm('Xác nhận thanh toán hóa đơn này?');">
                            <i class="fas fa-credit-card me-2"></i>Thanh toán ngay
                        </a>
                    <?php else: ?>
                        <div class="alert alert-success mb-0"><i class="fas fa-check-circle me-2"></i>Hóa đơn đã được thanh toán.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

