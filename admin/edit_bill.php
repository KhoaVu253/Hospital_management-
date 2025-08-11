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
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $consultation_fee = (float)$_POST['consultation_fee'];
        $medicine_fee = (float)$_POST['medicine_fee'];
        $other_fees = (float)$_POST['other_fees'];
        $status = $_POST['status'];
        
        // Tính tổng
        $total_amount = $consultation_fee + $medicine_fee + $other_fees;
        
        // Cập nhật hóa đơn
        $sql = "UPDATE bills SET 
                consultation_fee = ?, 
                medicine_fee = ?, 
                other_fees = ?, 
                total_amount = ?, 
                status = ?";
        
        // Nếu trạng thái là 'paid', cập nhật ngày thanh toán
        if ($status == 'paid') {
            $sql .= ", payment_date = NOW()";
        }
        
        $sql .= " WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($status == 'paid') {
            $stmt->bind_param("ddddsi", $consultation_fee, $medicine_fee, $other_fees, $total_amount, $status, $bill_id);
        } else {
            $stmt->bind_param("ddddsi", $consultation_fee, $medicine_fee, $other_fees, $total_amount, $status, $bill_id);
        }
        
        if ($stmt->execute()) {
            $message = "Cập nhật hóa đơn thành công!";
        } else {
            $error = "Lỗi khi cập nhật hóa đơn: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Lấy thông tin hóa đơn hiện tại
    $sql = "SELECT b.*, a.appointment_date, u1.full_name as patient_name, u2.full_name as doctor_name
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
        header("Location: manage_bills.php");
        exit();
    }
    
    $bill = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: manage_bills.php");
    exit();
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row"><main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Chỉnh sửa hóa đơn</h1>
                <a href="manage_bills.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

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
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="patient_name" class="form-label">Bệnh nhân</label>
                                            <input type="text" class="form-control" id="patient_name" 
                                                   value="<?php echo htmlspecialchars($bill['patient_name']); ?>" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="doctor_name" class="form-label">Bác sĩ</label>
                                            <input type="text" class="form-control" id="doctor_name" 
                                                   value="<?php echo htmlspecialchars($bill['doctor_name']); ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="appointment_date" class="form-label">Ngày khám</label>
                                            <input type="text" class="form-control" id="appointment_date" 
                                                   value="<?php echo formatDateTime($bill['appointment_date']); ?>" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Trạng thái</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="unpaid" <?php echo ($bill['status'] == 'unpaid') ? 'selected' : ''; ?>>Chờ thanh toán</option>
                                                <option value="paid" <?php echo ($bill['status'] == 'paid') ? 'selected' : ''; ?>>Đã thanh toán</option>
                                                <option value="cancelled" <?php echo ($bill['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="consultation_fee" class="form-label">Phí khám (VNĐ)</label>
                                            <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" 
                                                   value="<?php echo $bill['consultation_fee']; ?>" min="0" step="1000" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="medicine_fee" class="form-label">Phí thuốc (VNĐ)</label>
                                            <input type="number" class="form-control" id="medicine_fee" name="medicine_fee" 
                                                   value="<?php echo $bill['medicine_fee']; ?>" min="0" step="1000" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="other_fees" class="form-label">Chi phí khác (VNĐ)</label>
                                            <input type="number" class="form-control" id="other_fees" name="other_fees" 
                                                   value="<?php echo $bill['other_fees']; ?>" min="0" step="1000" required>
                                        </div>
                                    </div>
                                </div>



                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Cập nhật
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Tổng cộng</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí khám:</span>
                                <span id="consultation_display"><?php echo formatCurrency($bill['consultation_fee']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Thuốc:</span>
                                <span id="medicine_display"><?php echo formatCurrency($bill['medicine_fee']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Chi phí khác:</span>
                                <span id="other_display"><?php echo formatCurrency($bill['other_fees']); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Tổng cộng:</span>
                                <span class="text-primary" id="total_display"><?php echo formatCurrency($bill['total_amount']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Cập nhật tổng tiền khi thay đổi các khoản phí
document.getElementById('consultation_fee').addEventListener('input', updateTotal);
document.getElementById('medicine_fee').addEventListener('input', updateTotal);
document.getElementById('other_fees').addEventListener('input', updateTotal);

function updateTotal() {
    const consultation = parseFloat(document.getElementById('consultation_fee').value) || 0;
    const medicine = parseFloat(document.getElementById('medicine_fee').value) || 0;
    const other = parseFloat(document.getElementById('other_fees').value) || 0;
    const total = consultation + medicine + other;
    
    document.getElementById('consultation_display').textContent = formatCurrency(consultation);
    document.getElementById('medicine_display').textContent = formatCurrency(medicine);
    document.getElementById('other_display').textContent = formatCurrency(other);
    document.getElementById('total_display').textContent = formatCurrency(total);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}
</script>

<?php include '../includes/footer.php'; ?>
