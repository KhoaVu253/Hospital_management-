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
    $medicine_id = (int)$_GET['id'];
    
    // Kiểm tra xem thuốc có tồn tại không
    $check_sql = "SELECT id, name FROM medicines WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $medicine_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        $error = "Thuốc không tồn tại!";
    } else {
        $medicine = $check_result->fetch_assoc();
        
        // Kiểm tra xem thuốc có được sử dụng trong đơn thuốc nào không
        $check_usage_sql = "SELECT COUNT(*) as count FROM prescriptions WHERE medicine_id = ?";
        $check_usage_stmt = $conn->prepare($check_usage_sql);
        $check_usage_stmt->bind_param("i", $medicine_id);
        $check_usage_stmt->execute();
        $usage_result = $check_usage_stmt->get_result();
        $usage_count = $usage_result->fetch_assoc()['count'];
        $check_usage_stmt->close();
        
        if ($usage_count > 0) {
            $error = "Không thể xóa thuốc này vì đã được sử dụng trong " . $usage_count . " đơn thuốc!";
        } else {
            // Xóa thuốc
            $delete_sql = "DELETE FROM medicines WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $medicine_id);
            
            if ($delete_stmt->execute()) {
                $message = "Xóa thuốc '" . htmlspecialchars($medicine['name']) . "' thành công!";
            } else {
                $error = "Lỗi khi xóa thuốc: " . $conn->error;
            }
            $delete_stmt->close();
        }
    }
    $check_stmt->close();
} else {
    $error = "Không có ID thuốc được cung cấp!";
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Xóa thuốc</h1>
                <a href="manage_medicines.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center">
                            <a href="manage_medicines.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> Xem danh sách thuốc
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center">
                            <a href="manage_medicines.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> Quay lại danh sách
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
