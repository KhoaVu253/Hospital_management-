<!-- Sidebar -->
<div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky">

        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_patients.php') ? 'active' : ''; ?>" href="manage_patients.php">
                    <i class="fas fa-users me-2"></i>
                    Quản lý Bệnh nhân
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_doctors.php') ? 'active' : ''; ?>" href="manage_doctors.php">
                    <i class="fas fa-user-md me-2"></i>
                    Quản lý Bác sĩ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_appointments.php' || basename($_SERVER['PHP_SELF']) == 'view_appointment.php') ? 'active' : ''; ?>" href="manage_appointments.php">
                    <i class="fas fa-calendar-check me-2"></i>
                    Quản lý Lịch khám
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_bills.php') ? 'active' : ''; ?>" href="manage_bills.php">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Quản lý Hóa đơn
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_medicines.php') ? 'active' : ''; ?>" href="manage_medicines.php">
                    <i class="fas fa-pills me-2"></i>
                    Quản lý Thuốc
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>
                    Báo cáo
                </a>
            </li>
        </ul>
    </div>
</div>
