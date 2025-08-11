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
    <!-- Hero Section -->
    <div class="row">
        <div class="col-12">
            <div class="hero-section text-center">
                <div class="container">
                    <div class="row align-items-center min-vh-75">
                        <div class="col-lg-6 text-lg-start">
                            <h1 class="display-4 fw-bold mb-4">
                                <i class="fas fa-hospital me-3 text-white"></i>
                                DUCKHOA Hospital
                            </h1>
                            <p class="lead mb-4 opacity-90">
                                Cơ sở y tế hiện đại với đội ngũ bác sĩ, điều dưỡng giàu kinh nghiệm, tận tâm.
                                Chúng tôi cung cấp dịch vụ khám chữa bệnh chất lượng cao, trang thiết bị tiên tiến.
                            </p>
                            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-lg-start justify-content-center">
                                <a href="auth/login.php" class="btn btn-light btn-lg px-4 py-3">
                                    <i class="fas fa-calendar-plus me-2"></i>Đặt lịch khám ngay
                                </a>
                                <a href="#services" class="btn btn-outline-light btn-lg px-4 py-3">
                                    <i class="fas fa-info-circle me-2"></i>Tìm hiểu thêm
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-5 mt-lg-0">
                            <div class="position-relative">
                                <div class="bg-white bg-opacity-10 rounded-4 p-4 backdrop-blur">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="bg-white bg-opacity-20 rounded-3 p-3 text-center">
                                                <i class="fas fa-user-md fa-2x mb-2"></i>
                                                <h5 class="mb-0">50+</h5>
                                                <small>Bác sĩ chuyên khoa</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white bg-opacity-20 rounded-3 p-3 text-center">
                                                <i class="fas fa-hospital fa-2x mb-2"></i>
                                                <h5 class="mb-0">24/7</h5>
                                                <small>Dịch vụ cấp cứu</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white bg-opacity-20 rounded-3 p-3 text-center">
                                                <i class="fas fa-award fa-2x mb-2"></i>
                                                <h5 class="mb-0">15+</h5>
                                                <small>Năm kinh nghiệm</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-white bg-opacity-20 rounded-3 p-3 text-center">
                                                <i class="fas fa-heart fa-2x mb-2"></i>
                                                <h5 class="mb-0">10K+</h5>
                                                <small>Bệnh nhân tin tưởng</small>
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
    </div>

    <!-- Giới thiệu bệnh viện -->
    <div class="row py-5 bg-light">
        <div class="col-12">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="mb-4">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Giới thiệu DUCKHOA Hospital
                        </h2>
                        <p class="lead">DUCKHOA Hospital là cơ sở y tế hiện đại với đội ngũ bác sĩ, điều dưỡng giàu kinh nghiệm, tận tâm. Chúng tôi cung cấp dịch vụ khám chữa bệnh chất lượng cao, trang thiết bị tiên tiến, không gian thân thiện, hướng tới chăm sóc toàn diện và nâng cao sức khỏe cho cộng đồng.</p>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Đội ngũ bác sĩ giàu kinh nghiệm</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Trang thiết bị hiện đại</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Dịch vụ chất lượng cao</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6>Không gian thân thiện</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="mb-4">
                            <i class="fas fa-images text-primary me-2"></i>
                            Hình ảnh DUCKHOA Hospital
                        </h2>
                        <div class="row">
                            <!-- Hình ảnh 1: Bác sĩ khám bệnh (Lớn) -->
                            <div class="col-12 mb-4">
                                <div class="card shadow-lg border-0 rounded-3 overflow-hidden hospital-card">
                                    <div class="position-relative">
                                        <div class="hospital-image-placeholder" style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                                            <!-- Background pattern -->
                                            <div class="position-absolute w-100 h-100" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 250%22><defs><linearGradient id=%22grad1%22 x1=%220%%22 y1=%220%%22 x2=%22100%%22 y2=%22100%%22><stop offset=%220%%22 style=%22stop-color:%23667eea;stop-opacity:1%22 /><stop offset=%22100%%22 style=%22stop-color:%23764ba2;stop-opacity:1%22 /></linearGradient><pattern id=%22pattern1%22 patternUnits=%22userSpaceOnUse%22 width=%2250%22 height=%2250%22><circle cx=%2225%22 cy=%2225%22 r=%222%22 fill=%22rgba(255,255,255,0.1)%22 /></pattern></defs><rect width=%22100%%22 height=%22100%%22 fill=%22url(%23grad1)%22 /><rect width=%22100%%22 height=%22100%%22 fill=%22url(%23pattern1)%22 /></svg>') center/cover; opacity: 0.8;"></div>
                                            
                                            <!-- Decorative shapes -->
                                            <div class="position-absolute top-0 start-0 m-4">
                                                <div class="bg-white bg-opacity-20 rounded-circle" style="width: 60px; height: 60px;"></div>
                                            </div>
                                            <div class="position-absolute bottom-0 end-0 m-4">
                                                <div class="bg-white bg-opacity-15 rounded-circle" style="width: 40px; height: 40px;"></div>
                                            </div>
                                            
                                            <div class="text-center position-relative z-index-1">
                                                <div class="d-flex justify-content-center align-items-center mb-3">
                                                    <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3 shadow">
                                                        <i class="fas fa-user-md fa-2x text-white"></i>
                                                    </div>
                                                    <div class="bg-white bg-opacity-20 rounded-circle p-3 shadow">
                                                        <i class="fas fa-stethoscope fa-2x text-white"></i>
                                                    </div>
                                                </div>
                                                <h4 class="mb-2 fw-bold text-white">Khám bệnh chuyên nghiệp</h4>
                                                <p class="mb-0 opacity-90 text-white">Đội ngũ bác sĩ giàu kinh nghiệm, tận tâm</p>
                                            </div>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-3">
                                            <span class="badge bg-primary fs-6 shadow">Chuyên nghiệp</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-stethoscope me-2"></i>
                                            Khám bệnh tận tâm
                                        </h6>
                                        <p class="card-text small text-muted">
                                            Bác sĩ chuyên khoa thực hiện khám bệnh với sự tận tâm và chuyên nghiệp, 
                                            đảm bảo chẩn đoán chính xác và điều trị hiệu quả.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hình ảnh 2: Chăm sóc trẻ em -->
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-lg border-0 rounded-3 overflow-hidden h-100 hospital-card">
                                    <div class="position-relative">
                                        <div class="hospital-image-placeholder" style="height: 180px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                                            <!-- Background pattern -->
                                            <div class="position-absolute w-100 h-100" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 300 180%22><defs><linearGradient id=%22grad2%22 x1=%220%%22 y1=%220%%22 x2=%22100%%22 y2=%22100%%22><stop offset=%220%%22 style=%22stop-color:%23f093fb;stop-opacity:1%22 /><stop offset=%22100%%22 style=%22stop-color:%23f5576c;stop-opacity:1%22 /></linearGradient><pattern id=%22pattern2%22 patternUnits=%22userSpaceOnUse%22 width=%2240%22 height=%2240%22><circle cx=%2220%22 cy=%2220%22 r=%221.5%22 fill=%22rgba(255,255,255,0.15)%22 /></pattern></defs><rect width=%22100%%22 height=%22100%%22 fill=%22url(%23grad2)%22 /><rect width=%22100%%22 height=%22100%%22 fill=%22url(%23pattern2)%22 /></svg>') center/cover; opacity: 0.7;"></div>
                                            
                                            <!-- Decorative elements -->
                                            <div class="position-absolute top-0 start-0 m-2">
                                                <div class="bg-white bg-opacity-20 rounded-circle" style="width: 30px; height: 30px;"></div>
                                            </div>
                                            
                                            <div class="text-center position-relative z-index-1">
                                                <div class="bg-white bg-opacity-20 rounded-circle p-2 d-inline-block mb-2 shadow">
                                                    <i class="fas fa-baby fa-2x text-white"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-white">Chăm sóc trẻ em</h6>
                                            </div>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-warning shadow">Nhi khoa</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-heart me-2"></i>
                                            Chăm sóc tận tình
                                        </h6>
                                        <p class="card-text small text-muted">
                                            Dịch vụ chăm sóc trẻ em với sự ân cần và chuyên môn cao, 
                                            tạo môi trường thân thiện cho các bé.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hình ảnh 3: Chăm sóc sản phụ -->
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-lg border-0 rounded-3 overflow-hidden h-100 hospital-card">
                                    <div class="position-relative">
                                        <div class="hospital-image-placeholder" style="height: 180px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                                            <!-- Background pattern -->
                                            <div class="position-absolute w-100 h-100" style="background: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 300 180%22><defs><linearGradient id=%22grad3%22 x1=%220%%22 y1=%220%%22 x2=%22100%%22 y2=%22100%%22><stop offset=%220%%22 style=%22stop-color:%234facfe;stop-opacity:1%22 /><stop offset=%22100%%22 style=%22stop-color:%2300f2fe;stop-opacity:1%22 /></linearGradient><pattern id=%22pattern3%22 patternUnits=%22userSpaceOnUse%22 width=%2240%22 height=%2240%22><circle cx=%2220%22 cy=%2220%22 r=%221.5%22 fill=%22rgba(255,255,255,0.15)%22 /></pattern></defs><rect width=%22100%%22 height=%22100%%22 fill=%22url(%23grad3)%22 /><rect width=%22100%%22 height=%22100%%22 fill=%22url(%23pattern3)%22 /></svg>') center/cover; opacity: 0.7;"></div>
                                            
                                            <!-- Decorative elements -->
                                            <div class="position-absolute bottom-0 end-0 m-2">
                                                <div class="bg-white bg-opacity-20 rounded-circle" style="width: 25px; height: 25px;"></div>
                                            </div>
                                            
                                            <div class="text-center position-relative z-index-1">
                                                <div class="bg-white bg-opacity-20 rounded-circle p-2 d-inline-block mb-2 shadow">
                                                    <i class="fas fa-female fa-2x text-white"></i>
                                                </div>
                                                <h6 class="mb-0 fw-bold text-white">Chăm sóc sản phụ</h6>
                                            </div>
                                        </div>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-info shadow">Sản khoa</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-baby-carriage me-2"></i>
                                            Dịch vụ toàn diện
                                        </h6>
                                        <p class="card-text small text-muted">
                                            Chăm sóc sản phụ và trẻ sơ sinh với trang thiết bị hiện đại 
                                            và đội ngũ y bác sĩ giàu kinh nghiệm.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thông tin bổ sung -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-primary border-0 rounded-3 shadow" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-award fa-2x me-3"></i>
                                        <div>
                                            <h6 class="mb-1">DUCKHOA Hospital - Nơi gửi gắm sức khỏe</h6>
                                            <small>Cam kết mang đến dịch vụ y tế chất lượng cao với sự tận tâm và chuyên nghiệp</small>
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

    <!-- Các chuyên khoa -->
    <div class="row py-4">
        <div class="col-12">
            <div class="container">
                <h2 class="text-center mb-4">
                    <i class="fas fa-stethoscope text-primary me-2"></i>
                    Các Chuyên Khoa Khám Bệnh
                </h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-heartbeat fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Nội tổng quát</h5>
                                <p class="card-text">Khám và điều trị các bệnh lý nội khoa thông thường.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-user-md fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Ngoại tổng quát</h5>
                                <p class="card-text">Phẫu thuật, điều trị các bệnh cần can thiệp ngoại khoa.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-baby fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Nhi khoa</h5>
                                <p class="card-text">Chăm sóc sức khỏe cho trẻ em.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-female fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Sản – Phụ khoa</h5>
                                <p class="card-text">Khám, điều trị bệnh phụ nữ, chăm sóc thai sản.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Tim mạch</h5>
                                <p class="card-text">Chẩn đoán và điều trị các bệnh về tim và mạch máu.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-head-side-cough fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Tai – Mũi – Họng</h5>
                                <p class="card-text">Khám, điều trị các bệnh đường hô hấp trên.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-tooth fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Răng – Hàm – Mặt</h5>
                                <p class="card-text">Điều trị các vấn đề răng miệng và thẩm mỹ.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Mắt</h5>
                                <p class="card-text">Khám và điều trị các bệnh lý về mắt.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-allergies fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Da liễu</h5>
                                <p class="card-text">Khám và điều trị các bệnh về da, tóc, móng.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm specialty-card">
                            <div class="card-body text-center">
                                <i class="fas fa-x-ray fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Chẩn đoán hình ảnh</h5>
                                <p class="card-text">Siêu âm, X-quang, CT, MRI phục vụ chẩn đoán bệnh.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 