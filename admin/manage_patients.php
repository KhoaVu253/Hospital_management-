<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

$message = getMessage();

// Xử lý xóa bệnh nhân
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $patient_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'patient'");
    $stmt->bind_param("i", $patient_id);
    if ($stmt->execute()) {
        showMessage('Xóa bệnh nhân thành công!', 'success');
    } else {
        showMessage('Có lỗi xảy ra khi xóa bệnh nhân!', 'danger');
    }
    header("Location: manage_patients.php");
    exit();
}

include '../includes/header.php';
include '../includes/top_navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main content -->
        <div class="col-12 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-users me-2"></i>
                    Quản lý Bệnh nhân
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_patient.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm bệnh nhân
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Bộ lọc tìm kiếm -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                                   placeholder="Tên, email, số điện thoại...">
                        </div>
                        <div class="col-md-2">
                            <label for="gender" class="form-label">Giới tính</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Tất cả</option>
                                <option value="male" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                                <option value="female" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                                <option value="other" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Tìm kiếm
                                </button>
                                <a href="manage_patients.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Xóa lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách bệnh nhân -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Danh sách bệnh nhân
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Giới tính</th>
                                    <th>Ngày sinh</th>
                                    <th>Địa chỉ</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Xây dựng câu truy vấn với bộ lọc
                                $where_conditions = ["role = 'patient'"];
                                $params = [];
                                $types = "";

                                if (isset($_GET['search']) && !empty($_GET['search'])) {
                                    $search = '%' . $_GET['search'] . '%';
                                    $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                                    $params[] = $search;
                                    $params[] = $search;
                                    $params[] = $search;
                                    $types .= "sss";
                                }

                                if (isset($_GET['gender']) && !empty($_GET['gender'])) {
                                    $where_conditions[] = "gender = ?";
                                    $params[] = $_GET['gender'];
                                    $types .= "s";
                                }

                                $where_clause = implode(' AND ', $where_conditions);
                                $sql = "SELECT * FROM users WHERE $where_clause ORDER BY created_at DESC";
                                
                                $stmt = $conn->prepare($sql);
                                if (!empty($params)) {
                                    $stmt->bind_param($types, ...$params);
                                }
                                $stmt->execute();
                                $result = $stmt->get_result();

                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                        <br><small class="text-muted">@<?php echo htmlspecialchars($row['username']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td>
                                        <?php
                                        $gender_text = '';
                                        switch($row['gender']) {
                                            case 'male':
                                                $gender_text = '<span class="badge bg-primary">Nam</span>';
                                                break;
                                            case 'female':
                                                $gender_text = '<span class="badge bg-pink">Nữ</span>';
                                                break;
                                            case 'other':
                                                $gender_text = '<span class="badge bg-secondary">Khác</span>';
                                                break;
                                            default:
                                                $gender_text = '<span class="text-muted">-</span>';
                                        }
                                        echo $gender_text;
                                        ?>
                                    </td>
                                    <td><?php echo $row['date_of_birth'] ? formatDate($row['date_of_birth']) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo formatDateTime($row['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_patient.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_patient.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="patient_appointments.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" title="Lịch khám">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                            <a href="manage_patients.php?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Xóa"
                                               onclick="return confirmDelete('Bạn có chắc chắn muốn xóa bệnh nhân này?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($result->num_rows == 0): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy bệnh nhân nào</h5>
                            <p class="text-muted">Thử thay đổi bộ lọc tìm kiếm hoặc thêm bệnh nhân mới</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 