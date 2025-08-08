<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền bệnh nhân
requirePatient();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="book_appointment.php">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Đặt lịch khám
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Lịch khám của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical_history.php">
                            <i class="fas fa-history me-2"></i>
                            Lịch sử khám bệnh
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_bills.php">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Hóa đơn của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-2"></i>
                            Hồ sơ cá nhân
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="book_appointment.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám
                    </a>
                </div>
            </div>

            <!-- Thông tin cá nhân -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user me-2"></i>
                                Thông tin cá nhân
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                                    <p><strong>Tên đăng nhập:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <a href="profile.php" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-2"></i>Cập nhật thông tin
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Tổng số lịch khám
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ?");
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $data = $result->fetch_assoc();
                                        echo $data['count'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Lịch khám đã hoàn thành
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'completed'");
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $data = $result->fetch_assoc();
                                        echo $data['count'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Lịch khám chờ xác nhận
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE patient_id = ? AND status = 'pending'");
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $data = $result->fetch_assoc();
                                        echo $data['count'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Tổng chi phí đã thanh toán
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $stmt = $conn->prepare("SELECT SUM(b.total_amount) as total FROM bills b 
                                                               JOIN appointments a ON b.appointment_id = a.id 
                                                               WHERE a.patient_id = ? AND b.status = 'paid'");
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $data = $result->fetch_assoc();
                                        echo formatCurrency($data['total'] ?? 0);
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lịch khám gần đây -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-calendar-check me-2"></i>
                                Lịch khám gần đây
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Bác sĩ</th>
                                            <th>Chuyên khoa</th>
                                            <th>Ngày khám</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT a.*, d.specialty, u.full_name as doctor_name 
                                                FROM appointments a 
                                                JOIN doctors d ON a.doctor_id = d.id 
                                                JOIN users u ON d.user_id = u.id 
                                                WHERE a.patient_id = ? 
                                                ORDER BY a.appointment_date DESC 
                                                LIMIT 5";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if ($result->num_rows > 0):
                                            while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                                            <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch($row['status']) {
                                                    case 'pending':
                                                        $status_class = 'badge bg-warning';
                                                        $status_text = 'Chờ xác nhận';
                                                        break;
                                                    case 'confirmed':
                                                        $status_class = 'badge bg-info';
                                                        $status_text = 'Đã xác nhận';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'badge bg-success';
                                                        $status_text = 'Hoàn thành';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'badge bg-danger';
                                                        $status_text = 'Đã hủy';
                                                        break;
                                                }
                                                ?>
                                                <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($row['status'] == 'pending'): ?>
                                                    <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirmDelete('Bạn có chắc chắn muốn hủy lịch khám này?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">Bạn chưa có lịch khám nào</p>
                                                <a href="book_appointment.php" class="btn btn-primary">
                                                    <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám ngay
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hóa đơn gần đây -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Hóa đơn gần đây
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
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
                                        <?php
                                        $sql = "SELECT b.*, a.appointment_date, u.full_name as doctor_name 
                                                FROM bills b 
                                                JOIN appointments a ON b.appointment_id = a.id 
                                                JOIN doctors d ON a.doctor_id = d.id 
                                                JOIN users u ON d.user_id = u.id 
                                                WHERE a.patient_id = ? 
                                                ORDER BY b.created_at DESC 
                                                LIMIT 5";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $_SESSION['user_id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if ($result->num_rows > 0):
                                            while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td><?php echo formatDateTime($row['appointment_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                            <td><?php echo formatCurrency($row['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = $row['status'] == 'paid' ? 'badge bg-success' : 'badge bg-warning';
                                                $status_text = $row['status'] == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
                                                ?>
                                                <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <a href="view_bill.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($row['status'] == 'unpaid'): ?>
                                                    <a href="pay_bill.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                                        <i class="fas fa-credit-card"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">Bạn chưa có hóa đơn nào</p>
                                            </td>
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
    </div>
</div>

<?php include '../includes/footer.php'; ?> 