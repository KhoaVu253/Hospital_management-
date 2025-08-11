<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">\n    <div class="row">\n        <!-- Main content -->\n        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Quản lý Hóa đơn
                </h1>
            </div>

            <!-- Bảng hóa đơn -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách hóa đơn</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Mã hóa đơn</th>
                                    <th>Bệnh nhân</th>
                                    <th>Bác sĩ</th>
                                    <th>Ngày khám</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT b.*, a.appointment_date, u1.full_name as patient_name, u2.full_name as doctor_name 
                                       FROM bills b 
                                       JOIN appointments a ON b.appointment_id = a.id 
                                       JOIN users u1 ON a.patient_id = u1.id 
                                       JOIN doctors d ON a.doctor_id = d.id 
                                       JOIN users u2 ON d.user_id = u2.id 
                                       ORDER BY b.created_at DESC";
                                $result = $conn->query($sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>BILL-" . str_pad($row['id'], 6, '0', STR_PAD_LEFT) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['patient_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['doctor_name']) . "</td>";
                                        echo "<td>" . formatDate($row['appointment_date']) . "</td>";
                                        echo "<td>" . formatCurrency($row['total_amount']) . "</td>";
                                        echo "<td>";
                                        switch($row['status']) {
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
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<a href='view_bill.php?id=" . $row['id'] . "' class='btn btn-sm btn-info me-1'><i class='fas fa-eye'></i></a>";
                                        echo "<a href='edit_bill.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary me-1'><i class='fas fa-edit'></i></a>";
                                        echo "<a href='delete_bill.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Bạn có chắc muốn xóa?\")'><i class='fas fa-trash'></i></a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>Không có hóa đơn nào</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 