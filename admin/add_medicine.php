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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $unit = trim($_POST['unit']);
    $price = (float)$_POST['price'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $manufacturer = trim($_POST['manufacturer']);
    $expiry_date = $_POST['expiry_date'];
    
    // Kiểm tra tên thuốc đã tồn tại chưa
    $check_sql = "SELECT id FROM medicines WHERE name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "Tên thuốc đã tồn tại!";
    } else {
        // Thêm thuốc mới
        $sql = "INSERT INTO medicines (name, description, unit, price, stock_quantity, manufacturer, expiry_date, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdiss", $name, $description, $unit, $price, $stock_quantity, $manufacturer, $expiry_date);
        
        if ($stmt->execute()) {
            $message = "Thêm thuốc mới thành công!";
            // Reset form
            $_POST = array();
        } else {
            $error = "Lỗi khi thêm thuốc: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

include '../includes/header.php';\ninclude '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row"><main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Thêm thuốc mới</h1>
                <a href="manage_medicines.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
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
                                    <label for="name" class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="manufacturer" class="form-label">Nhà sản xuất</label>
                                    <input type="text" class="form-control" id="manufacturer" name="manufacturer" 
                                           value="<?php echo htmlspecialchars($_POST['manufacturer'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Đơn vị</label>
                                    <input type="text" class="form-control" id="unit" name="unit" 
                                           value="<?php echo htmlspecialchars($_POST['unit'] ?? ''); ?>" 
                                           placeholder="Ví dụ: viên, chai, gói">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Hạn sử dụng</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                           value="<?php echo $_POST['expiry_date'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           value="<?php echo $_POST['price'] ?? ''; ?>" min="0" step="1000" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                           value="<?php echo $_POST['stock_quantity'] ?? ''; ?>" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Mô tả về thuốc, cách sử dụng, tác dụng phụ..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm thuốc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
