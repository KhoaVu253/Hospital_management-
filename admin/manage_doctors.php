<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

$message = getMessage();

// Xử lý xóa bác sĩ
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $doctor_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $doctor_id);
    if ($stmt->execute()) {
        showMessage('Xóa bác sĩ thành công!', 'success');
    } else {
        showMessage('Có lỗi xảy ra khi xóa bác sĩ!', 'danger');
    }
    header("Location: manage_doctors.php");
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
                    <i class="fas fa-user-md me-2"></i>
                    Quản lý Bác sĩ
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_doctor.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm bác sĩ
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
                                   placeholder="Tên, chuyên khoa, email...">
                        </div>
                        <div class="col-md-2">
                            <label for="specialty" class="form-label">Chuyên khoa</label>
                            <select class="form-select" id="specialty" name="specialty">
                                <option value="">Tất cả</option>
                                <option value="Tim mạch" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] == 'Tim mạch') ? 'selected' : ''; ?>>Tim mạch</option>
                                <option value="Nhi khoa" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] == 'Nhi khoa') ? 'selected' : ''; ?>>Nhi khoa</option>
                                <option value="Da liễu" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] == 'Da liễu') ? 'selected' : ''; ?>>Da liễu</option>
                                <option value="Thần kinh" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] == 'Thần kinh') ? 'selected' : ''; ?>>Thần kinh</option>
                                <option value="Tiêu hóa" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] == 'Tiêu hóa') ? 'selected' : ''; ?>>Tiêu hóa</option>
                                <option value="Ngoại khoa" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] == 'Ngoại khoa') ? 'selected' : ''; ?>>Ngoại khoa</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tất cả</option>
                                <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Đang làm việc</option>
                                <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Ngừng làm việc</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Tìm kiếm
                                </button>
                                <a href="manage_doctors.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Xóa lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danh sách bác sĩ -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        Danh sách bác sĩ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Họ tên</th>
                                    <th>Chuyên khoa</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Kinh nghiệm</th>
                                    <th>Phí khám bệnh</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Xây dựng câu truy vấn với bộ lọc
                                $where_conditions = [];
                                $params = [];
                                $types = "";

                                if (isset($_GET['search']) && !empty($_GET['search'])) {
                                    $search = '%' . $_GET['search'] . '%';
                                    $where_conditions[] = "(u.full_name LIKE ? OR d.specialty LIKE ? OR u.email LIKE ?)";
                                    $params[] = $search;
                                    $params[] = $search;
                                    $params[] = $search;
                                    $types .= "sss";
                                }

                                if (isset($_GET['specialty']) && !empty($_GET['specialty'])) {
                                    $where_conditions[] = "d.specialty = ?";
                                    $params[] = $_GET['specialty'];
                                    $types .= "s";
                                }

                                if (isset($_GET['status']) && !empty($_GET['status'])) {
                                    $where_conditions[] = "d.status = ?";
                                    $params[] = $_GET['status'];
                                    $types .= "s";
                                }

                                $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
                                $sql = "SELECT d.*, u.full_name, u.email, u.phone, u.gender 
                                        FROM doctors d 
                                        JOIN users u ON d.user_id = u.id 
                                        $where_clause 
                                        ORDER BY d.created_at DESC";
                                
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
                                        <br><small class="text-muted">Số chứng chỉ: <?php echo htmlspecialchars($row['license_number']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($row['specialty']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo $row['experience_years']; ?> năm</td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo number_format($row['consultation_fee']); ?> VNĐ</span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = $row['status'] == 'active' ? 'badge bg-success' : 'badge bg-danger';
                                        $status_text = $row['status'] == 'active' ? 'Đang làm việc' : 'Ngừng làm việc';
                                        ?>
                                        <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_doctor.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_doctor.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="doctor_appointments.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" title="Lịch khám">
                                                <i class="fas fa-calendar"></i>
                                            </a>
                                            <a href="manage_doctors.php?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Xóa"
                                               onclick="return confirmDelete('Bạn có chắc chắn muốn xóa bác sĩ này?')">
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
                            <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy bác sĩ nào</h5>
                            <p class="text-muted">Thử thay đổi bộ lọc tìm kiếm hoặc thêm bác sĩ mới</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 