<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DUCKHOA Hospital - Hệ thống Quản lý Bệnh viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>public/css/style.css">
    <style>
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffffff !important;
        }
        .navbar-brand i {
            font-size: 1.8rem;
            color: #ffffff;
        }
        .nav-link {
            font-size: 1.1rem;
            font-weight: 500;
            color: #ffffff !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: var(--primary-light) !important;
            transform: translateY(-1px);
        }
        .navbar-nav .nav-item {
            margin-left: 0.5rem;
        }
        .navbar-nav .nav-link {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 0.5rem 0;
        }
        .dropdown-item {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .dropdown-item:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body class="dark-theme">
