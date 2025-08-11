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
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $unit = trim($_POST['unit']);
        $price = (float)$_POST['price'];
        $stock_quantity = (int)$_POST['stock_quantity'];
        $manufacturer = trim($_POST['manufacturer']);
        $expiry_date = $_POST['expiry_date'];
        
        // Cập nhật thuốc
        $sql = "UPDATE medicines SET 
                name = ?, 
                description = ?, 
                unit = ?, 
                price = ?, 
                stock_quantity = ?, 
                manufacturer = ?, 
                expiry_date = ? 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdissi", $name, $description, $unit, $price, $stock_quantity, $manufacturer, $expiry_date, $medicine_id);
        
        if ($stmt->execute()) {
            $message = "Cập nhật thuốc thành công!";
        } else {
            $error = "Lỗi khi cập nhật thuốc: " . $conn->error;
        }
        $stmt->close();
    }
    
    // Lấy thông tin thuốc hiện tại
    $sql = "SELECT * FROM medicines WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $medicine_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: manage_medicines.php");
        exit();
    }
    
    $medicine = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: manage_medicines.php");
    exit();
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row"><main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Chỉnh sửa thuốc</h1>
                <a href="manage_medicines.php" class="btn btn-secondary">
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

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên thuốc</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($medicine['name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manufacturer" class="form-label">Nhà sản xuất</label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                           value="<?php echo htmlspecialchars($medicine['manufacturer'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Đơn vị</label>
                                    <input type="text" class="form-control" id="unit" name="unit" 
                                           value="<?php echo htmlspecialchars($medicine['unit'] ?? ''); ?>" 
                                           placeholder="Ví dụ: viên, chai, gói">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Hạn sử dụng</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                           value="<?php echo $medicine['expiry_date'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ)</label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           value="<?php echo $medicine['price']; ?>" min="0" step="1000" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                           value="<?php echo $medicine['stock_quantity']; ?>" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Mô tả về thuốc, cách sử dụng, tác dụng phụ..."><?php echo htmlspecialchars($medicine['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
