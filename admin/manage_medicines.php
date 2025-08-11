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
                    <i class="fas fa-pills me-2"></i>
                    Quản lý Thuốc
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_medicine.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm thuốc mới
                    </a>
                </div>
            </div>

            <!-- Bảng thuốc -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách thuốc</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên thuốc</th>
                                    <th>Mô tả</th>
                                    <th>Đơn vị</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM medicines ORDER BY name ASC";
                                $result = $conn->query($sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
                                        echo "<td>" . htmlspecialchars($row['unit'] ?? '') . "</td>";
                                        echo "<td>" . formatCurrency($row['price']) . "</td>";
                                        echo "<td>" . $row['stock_quantity'] . "</td>";
                                        echo "<td>";
                                        if ($row['stock_quantity'] > 0) {
                                            echo "<span class='badge bg-success'>Có sẵn</span>";
                                        } else {
                                            echo "<span class='badge bg-danger'>Hết hàng</span>";
                                        }
                                        echo "</td>";
                                        echo "<td>";
                                        echo "<a href='edit_medicine.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary me-1'><i class='fas fa-edit'></i></a>";
                                        echo "<a href='delete_medicine.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Bạn có chắc muốn xóa?\")'><i class='fas fa-trash'></i></a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center'>Không có thuốc nào</td></tr>";
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