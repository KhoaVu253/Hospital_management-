<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Nếu đã đăng nhập thì chuyển hướng theo vai trò
if (isLoggedIn()) {
    if (isPatient()) {
        header('Location: dashboard.php');
    } elseif (isAdmin()) {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../doctor/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = sanitizeInput($_POST['username']);
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE (username = ? OR email = ?) AND role = "patient"');
        $stmt->bind_param('ss', $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (verifyPassword($password, $user['password'])) {
                // Đăng nhập thành công cho bệnh nhân
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Mật khẩu không đúng!';
            }
        } else {
            $error = 'Tài khoản bệnh nhân không tồn tại!';
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-hospital fa-3x text-primary mb-3"></i>
                        <h3 class="card-title">DUCKHOA Hospital</h3>
                        <h5 class="text-muted">Đăng nhập Bệnh nhân</h5>
                        <p class="text-muted">Vui lòng đăng nhập để tiếp tục</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-2"></i>Tên đăng nhập hoặc Email
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Mật khẩu
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Bạn không phải bệnh nhân?
                            <a href="../auth/login.php" class="text-decoration-none">Đăng nhập trang khác</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

