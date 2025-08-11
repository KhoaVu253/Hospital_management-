<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-calendar-check me-2"></i>
                    Quản lý Lịch khám
                </h1>
            </div>

            <!-- Bảng lịch khám -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách lịch khám</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Bệnh nhân</th>
                                    <th>Bác sĩ</th>
                                    <th>Ngày khám</th>
                                    <th>Trạng thái</th>
                                    <th>Chẩn đoán</th>
                                    <th>Đơn thuốc</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT a.*, u1.full_name as patient_name, u2.full_name as doctor_name 
                                       FROM appointments a 
                                       JOIN users u1 ON a.patient_id = u1.id 
                                       JOIN doctors d ON a.doctor_id = d.id 
                                       JOIN users u2 ON d.user_id = u2.id 
                                       ORDER BY a.appointment_date DESC";
                                $result = $conn->query($sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['patient_name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['doctor_name']) . "</td>";
                                        echo "<td>" . formatDateTime($row['appointment_date']) . "</td>";
                                        echo "<td>";
                                        switch($row['status']) {
                                            case 'pending':
                                                echo "<span class='badge bg-warning'>Chờ xác nhận</span>";
                                                break;
                                            case 'confirmed':
                                                echo "<span class='badge bg-success'>Đã xác nhận</span>";
                                                break;
                                            case 'completed':
                                                echo "<span class='badge bg-info'>Hoàn thành</span>";
                                                break;
                                            case 'cancelled':
                                                echo "<span class='badge bg-danger'>Đã hủy</span>";
                                                break;
                                        }
                                        echo "</td>";
                                        echo "<td>";
                                        if (!empty($row['diagnosis'])) {
                                            echo "<span class='text-success'><i class='fas fa-check-circle'></i> Có</span>";
                                        } else {
                                            echo "<span class='text-muted'><i class='fas fa-times-circle'></i> Chưa có</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>";
                                        if (!empty($row['prescription'])) {
                                            echo "<span class='text-success'><i class='fas fa-check-circle'></i> Có</span>";
                                        } else {
                                            echo "<span class='text-muted'><i class='fas fa-times-circle'></i> Chưa có</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<a href='view_appointment.php?id=" . $row['id'] . "' class='btn btn-sm btn-info me-1' title='Xem chi tiết'><i class='fas fa-eye'></i></a>";
                                        echo "<a href='edit_appointment.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary me-1' title='Chỉnh sửa'><i class='fas fa-edit'></i></a>";
                                        echo "<a href='delete_appointment.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Bạn có chắc muốn xóa?\")' title='Xóa'><i class='fas fa-trash'></i></a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center'>Không có lịch khám nào</td></tr>";
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