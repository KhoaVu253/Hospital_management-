<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Chuyển hướng nếu đã đăng nhập
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } elseif (isDoctor()) {
        header("Location: doctor/dashboard.php");
    } else {
        header("Location: patient/dashboard.php");
    }
    exit();
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="hero-section text-center py-5">
                <div class="container">
                    <h1 class="display-4 mb-4">
                        <i class="fas fa-hospital me-3"></i>
                        Hệ thống Quản lý Bệnh viện
                    </h1>
                    <p class="lead mb-5">Giải pháp quản lý bệnh viện hiện đại, an toàn và hiệu quả</p>
                    <div class="row justify-content-center">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-white text-dark">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                                    <h5>Quản lý Bác sĩ</h5>
                                    <p class="text-muted">Quản lý thông tin và lịch làm việc của bác sĩ</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-white text-dark">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                    <h5>Quản lý Bệnh nhân</h5>
                                    <p class="text-muted">Lưu trữ và quản lý hồ sơ bệnh nhân</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-white text-dark">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                                    <h5>Đặt lịch khám</h5>
                                    <p class="text-muted">Đặt lịch khám trực tuyến nhanh chóng</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="auth/login.php" class="btn btn-light btn-lg me-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                        <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row py-5">
        <div class="col-12">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="mb-4">Tính năng chính</h2>
                        <div class="row">
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Quản lý bệnh nhân</h6>
                                        <small class="text-muted">Lưu trữ thông tin bệnh nhân</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Quản lý bác sĩ</h6>
                                        <small class="text-muted">Quản lý thông tin bác sĩ</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Đặt lịch khám</h6>
                                        <small class="text-muted">Đặt lịch khám trực tuyến</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Quản lý hóa đơn</h6>
                                        <small class="text-muted">Tính toán và quản lý chi phí</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-4">Thống kê</h2>
                        <div class="row">
                            <div class="col-6 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-user-md fa-2x text-primary mb-2"></i>
                                        <h4 class="text-primary">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'");
                                            $data = $result->fetch_assoc();
                                            echo $data['count'];
                                            ?>
                                        </h4>
                                        <p class="mb-0">Bác sĩ</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                                        <h4 class="text-success">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'patient'");
                                            $data = $result->fetch_assoc();
                                            echo $data['count'];
                                            ?>
                                        </h4>
                                        <p class="mb-0">Bệnh nhân</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-calendar-check fa-2x text-info mb-2"></i>
                                        <h4 class="text-info">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'");
                                            $data = $result->fetch_assoc();
                                            echo $data['count'];
                                            ?>
                                        </h4>
                                        <p class="mb-0">Lịch khám chờ</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-calendar-day fa-2x text-warning mb-2"></i>
                                        <h4 class="text-warning">
                                            <?php
                                            $result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()");
                                            $data = $result->fetch_assoc();
                                            echo $data['count'];
                                            ?>
                                        </h4>
                                        <p class="mb-0">Khám hôm nay</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 