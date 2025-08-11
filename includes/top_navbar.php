<?php
// Top Navigation Bar Component
// Usage: include '../includes/top_navbar.php';

// Add class to body for proper spacing
echo '<script>document.body.classList.add("has-top-navbar");</script>';

// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Get user role and name from session
$user_role = $_SESSION['role'] ?? '';
$user_name = $_SESSION['full_name'] ?? '';

// Define navigation items based on user role
$nav_items = [];

if ($user_role === 'admin') {
    $nav_items = [
        ['url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard'],
        ['url' => 'manage_patients.php', 'icon' => 'fas fa-users', 'text' => 'Quản lý Bệnh nhân'],
        ['url' => 'manage_doctors.php', 'icon' => 'fas fa-user-md', 'text' => 'Quản lý Bác sĩ'],
        ['url' => 'manage_appointments.php', 'icon' => 'fas fa-calendar-check', 'text' => 'Quản lý Lịch khám'],
        ['url' => 'manage_bills.php', 'icon' => 'fas fa-file-invoice-dollar', 'text' => 'Quản lý Hóa đơn'],
        ['url' => 'manage_medicines.php', 'icon' => 'fas fa-pills', 'text' => 'Quản lý Thuốc'],
        ['url' => 'reports.php', 'icon' => 'fas fa-chart-bar', 'text' => 'Báo cáo']
    ];
    $role_text = 'Quản trị viên';
    $user_icon = 'fas fa-user-circle';
} elseif ($user_role === 'doctor') {
    $nav_items = [
        ['url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard'],
        ['url' => 'appointments.php', 'icon' => 'fas fa-calendar-check', 'text' => 'Lịch khám'],
        ['url' => 'prescriptions.php', 'icon' => 'fas fa-pills', 'text' => 'Kê thuốc'],
        ['url' => 'profile.php', 'icon' => 'fas fa-user', 'text' => 'Thông tin cá nhân']
    ];
    $role_text = 'Bác sĩ';
    $user_icon = 'fas fa-user-md';
} elseif ($user_role === 'patient') {
    $nav_items = [
        ['url' => 'dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'text' => 'Dashboard'],
        ['url' => 'book_appointment.php', 'icon' => 'fas fa-calendar-plus', 'text' => 'Đặt lịch khám'],
        ['url' => 'my_appointments.php', 'icon' => 'fas fa-calendar-check', 'text' => 'Lịch khám của tôi'],
        ['url' => 'medical_history.php', 'icon' => 'fas fa-history', 'text' => 'Lịch sử khám bệnh'],
        ['url' => 'my_bills.php', 'icon' => 'fas fa-file-invoice-dollar', 'text' => 'Hóa đơn của tôi'],
        ['url' => 'profile.php', 'icon' => 'fas fa-user', 'text' => 'Hồ sơ cá nhân']
    ];
    $role_text = 'Bệnh nhân';
    $user_icon = 'fas fa-user';
}

// Build profile and change password URLs per role
$profile_url = 'profile.php';
$change_password_url = 'profile.php';
if ($user_role === 'admin') {
    $profile_url = getBaseUrl() . 'profile.php';
    $change_password_url = getBaseUrl() . 'profile.php';
} elseif ($user_role === 'doctor') {
    $profile_url = 'profile.php';
    $change_password_url = 'change_password.php';
} elseif ($user_role === 'patient') {
    $profile_url = 'profile.php'; // redirects to root profile
    $change_password_url = 'profile.php';
}

?>

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg top-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-hospital me-2"></i>
            DUCKHOA Hospital
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar" aria-controls="topNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topNavbar">
            <ul class="navbar-nav me-auto">
                <?php foreach ($nav_items as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === $item['url']) ? 'active' : ''; ?>"
                           href="<?php echo $item['url']; ?>">
                            <i class="<?php echo $item['icon']; ?> me-2"></i>
                            <?php echo $item['text']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-info me-2">
                            <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                            <div class="user-role"><?php echo $role_text; ?></div>
                        </div>
                        <i class="<?php echo $user_icon; ?> fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $profile_url; ?>">
                            <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo $change_password_url; ?>">
                            <i class="fas fa-key me-2"></i>Đổi mật khẩu
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
