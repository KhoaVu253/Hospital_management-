<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

include 'includes/header.php';
?>

<style>
    body.has-top-navbar {
        padding-top: 160px;
    }
</style>

<script>
    document.body.classList.add('has-top-navbar');
</script>

<!-- Demo Top Navigation Bar -->
<nav class="navbar navbar-expand-lg top-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="demo.php">
            <i class="fas fa-hospital me-2"></i>
            DUCKHOA Hospital Demo
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="topNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#components">
                        <i class="fas fa-puzzle-piece me-2"></i>Components
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#cards">
                        <i class="fas fa-layer-group me-2"></i>Cards
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#forms">
                        <i class="fas fa-edit me-2"></i>Forms
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#tables">
                        <i class="fas fa-table me-2"></i>Tables
                    </a>
                </li>
            </ul>
            
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <div class="user-info me-2">
                            <div class="user-name">Demo User</div>
                            <div class="user-role">Administrator</div>
                        </div>
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="fas fa-palette me-2 text-primary"></i>
                            UI Components Demo
                        </h1>
                        <p class="mb-0">
                            <i class="fas fa-info-circle me-2 text-muted"></i>
                            Showcase of all UI components and styles
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="HospitalApp.showNotification('This is a test notification!', 'info')">
                            <i class="fas fa-bell me-2"></i>Test Notification
                        </button>
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-5" id="components">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-users text-primary"></i>
                        <h4 class="text-primary">1,234</h4>
                        <p class="text-muted mb-0">Total Users</p>
                        <small class="text-success">
                            <i class="fas fa-arrow-up me-1"></i>+12% from last month
                        </small>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-chart-line text-success"></i>
                        <h4 class="text-success">$45,678</h4>
                        <p class="text-muted mb-0">Revenue</p>
                        <small class="text-info">
                            <i class="fas fa-check-circle me-1"></i>Target achieved
                        </small>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-shopping-cart text-warning"></i>
                        <h4 class="text-warning">567</h4>
                        <p class="text-muted mb-0">Orders</p>
                        <small class="text-primary">
                            <i class="fas fa-clock me-1"></i>Updated realtime
                        </small>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <i class="fas fa-star text-info"></i>
                        <h4 class="text-info">4.8</h4>
                        <p class="text-muted mb-0">Rating</p>
                        <small class="text-success">
                            <i class="fas fa-trending-up me-1"></i>+0.3 from last week
                        </small>
                    </div>
                </div>
            </div>

            <!-- Cards Section -->
            <div class="row mb-5" id="cards">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                Sample Chart Card
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>This is a sample card with chart content. You can put any content here.</p>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Progress:</span>
                                <span class="fw-bold">75%</span>
                            </div>
                            <div class="progress mt-2">
                                <div class="progress-bar bg-primary" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2 text-success"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>Add New Item
                                </button>
                                <button class="btn btn-outline-success">
                                    <i class="fas fa-edit me-2"></i>Edit Settings
                                </button>
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="mb-3">Alert Components</h3>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> Your action was completed successfully.
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning!</strong> Please check your input data.
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Error!</strong> Something went wrong. Please try again.
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Info!</strong> Here's some helpful information for you.
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="mb-3">Button Components</h3>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button class="btn btn-primary">Primary</button>
                        <button class="btn btn-success">Success</button>
                        <button class="btn btn-warning">Warning</button>
                        <button class="btn btn-danger">Danger</button>
                        <button class="btn btn-info">Info</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button class="btn btn-outline-primary">Outline Primary</button>
                        <button class="btn btn-outline-success">Outline Success</button>
                        <button class="btn btn-outline-warning">Outline Warning</button>
                        <button class="btn btn-outline-danger">Outline Danger</button>
                        <button class="btn btn-outline-info">Outline Info</button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-primary">Small</button>
                        <button class="btn btn-primary">Normal</button>
                        <button class="btn btn-lg btn-primary">Large</button>
                    </div>
                </div>
            </div>

            <!-- Badges -->
            <div class="row mb-5">
                <div class="col-12">
                    <h3 class="mb-3">Badge Components</h3>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-primary">Primary</span>
                        <span class="badge bg-success">Success</span>
                        <span class="badge bg-warning">Warning</span>
                        <span class="badge bg-danger">Danger</span>
                        <span class="badge bg-info">Info</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
