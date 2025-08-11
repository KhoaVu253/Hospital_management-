<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

requirePatient();

$billId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($billId <= 0) {
    header('Location: my_bills.php');
    exit();
}

// Xác thực hóa đơn thuộc về bệnh nhân hiện tại
$sql = "SELECT b.id FROM bills b JOIN appointments a ON b.appointment_id = a.id WHERE b.id = ? AND a.patient_id = ? AND b.status = 'unpaid'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $billId, $_SESSION['user_id']);
$stmt->execute();
$valid = $stmt->get_result()->fetch_assoc();

if (!$valid) {
    showMessage('Không thể thanh toán hóa đơn này!', 'danger');
    header('Location: my_bills.php');
    exit();
}

// Cập nhật trạng thái thanh toán
$update = $conn->prepare("UPDATE bills SET status = 'paid', payment_date = NOW() WHERE id = ?");
$update->bind_param('i', $billId);
if ($update->execute()) {
    showMessage('Thanh toán thành công!', 'success');
} else {
    showMessage('Có lỗi xảy ra khi thanh toán!', 'danger');
}

header('Location: view_bill.php?id=' . $billId);
exit();

