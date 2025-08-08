<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
requireAdmin();

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
                        <a class="nav-link" href="manage_patients.php">
                            <i class="fas fa-users me-2"></i>
                            Quản lý Bệnh nhân
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_doctors.php">
                            <i class="fas fa-user-md me-2"></i>
                            Quản lý Bác sĩ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_appointments.php">
                            <i class="fas fa-calendar-check me-2"></i>
                            Quản lý Lịch khám
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_bills.php">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Quản lý Hóa đơn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_medicines.php">
                            <i class="fas fa-pills me-2"></i>
                            Quản lý Thuốc
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i>
                            Báo cáo
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
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Xuất báo cáo</button>
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
                                        Tổng số bệnh nhân
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'patient'");
                                        $data = $result->fetch_assoc();
                                        echo $data['count'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                        Tổng số bác sĩ
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $result = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'");
                                        $data = $result->fetch_assoc();
                                        echo $data['count'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-md fa-2x text-gray-300"></i>
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
                                        Lịch khám hôm nay
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()");
                                        $data = $result->fetch_assoc();
                                        echo $data['count'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                        Doanh thu tháng
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                        $result = $conn->query("SELECT SUM(total_amount) as total FROM bills WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'paid'");
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
                                            <th>Bệnh nhân</th>
                                            <th>Bác sĩ</th>
                                            <th>Ngày khám</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT a.*, p.full_name as patient_name, d.specialty, doc.full_name as doctor_name 
                                                FROM appointments a 
                                                JOIN users p ON a.patient_id = p.id 
                                                JOIN doctors d ON a.doctor_id = d.id 
                                                JOIN users doc ON d.user_id = doc.id 
                                                ORDER BY a.appointment_date DESC 
                                                LIMIT 10";
                                        $result = $conn->query($sql);
                                        while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['doctor_name']); ?> (<?php echo htmlspecialchars($row['specialty']); ?>)</td>
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
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ thống kê -->
            <div class="row">
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-area me-2"></i>
                                Thống kê lịch khám theo tháng
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="appointmentChart" width="100%" height="40"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-chart-pie me-2"></i>
                                Phân bố chuyên khoa
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="specialtyChart" width="100%" height="40"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ lịch khám theo tháng
const appointmentCtx = document.getElementById('appointmentChart').getContext('2d');
const appointmentChart = new Chart(appointmentCtx, {
    type: 'line',
    data: {
        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        datasets: [{
            label: 'Số lịch khám',
            data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 12],
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Biểu đồ phân bố chuyên khoa
const specialtyCtx = document.getElementById('specialtyChart').getContext('2d');
const specialtyChart = new Chart(specialtyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Tim mạch', 'Nhi khoa', 'Da liễu', 'Thần kinh', 'Tiêu hóa'],
        datasets: [{
            data: [30, 25, 15, 20, 10],
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>

<?php include '../includes/footer.php'; ?> 