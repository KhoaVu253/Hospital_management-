<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

requirePatient();

include '../includes/header.php';
include '../includes/top_navbar.php';

$patientId = $_SESSION['user_id'];

$sql = "SELECT b.*, a.appointment_date, u.full_name as doctor_name
        FROM bills b
        JOIN appointments a ON b.appointment_id = a.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE a.patient_id = ?
        ORDER BY b.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $patientId);
$stmt->execute();
$bills = $stmt->get_result();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Hóa đơn của tôi
                </h1>
            </div>

            <?php $flash = getMessage(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách hóa đơn</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Mã hóa đơn</th>
                                    <th>Ngày khám</th>
                                    <th>Bác sĩ</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($bills && $bills->num_rows > 0): ?>
                                    <?php while ($row = $bills->fetch_assoc()): ?>
                                        <tr>
                                            <td>BILL-<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                if ($row['status'] === 'paid') {
                                                    echo "<span class='badge bg-success'>Đã thanh toán</span>";
                                                } elseif ($row['status'] === 'cancelled') {
                                                    echo "<span class='badge bg-danger'>Đã hủy</span>";
                                                } else {
                                                    echo "<span class='badge bg-warning text-dark'>Chưa thanh toán</span>";
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-info me-1" href="view_bill.php?id=<?php echo (int)$row['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($row['status'] === 'unpaid'): ?>
                                                    <a class="btn btn-sm btn-success" href="pay_bill.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Xác nhận thanh toán hóa đơn này?');">
                                                        <i class="fas fa-credit-card"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Bạn chưa có hóa đơn nào.</td>
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

