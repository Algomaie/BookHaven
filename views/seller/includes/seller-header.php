<?php
// includes/seller-header.php
// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../../auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - BookHub</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #5D5CDE;
            --primary-dark: #4A49B0;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }
        
        /* Sidebar styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            z-index: 100;
            padding: 56px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: var(--primary);
            background-image: linear-gradient(180deg, var(--primary) 10%, var(--primary-dark) 100%);
            background-size: cover;
            transition: all 0.3s;
            
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 56px);
            padding-top: 0.5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            color: rgba(255, 255, 255, 0.3);
        }
        
        .sidebar .nav-link:hover i, .sidebar .nav-link.active i {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
        }
        
        /* Main content wrapper */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: 56px;
            transition: all 0.3s;
        }
        
        /* Topbar styles */
        .topbar {
            background-color: #fff;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 99;
            height: 56px;
            transition: all 0.3s;
        }
        
        /* Card styles */
        .card {
            border: none;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.1);
            transition: all 0.3s ease;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        /* Form styles */
        .form-control:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(93, 92, 222, 0.25);
        }
        
        /* Button styles */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        /* Border utilities */
        .border-left-primary {
            border-left: 4px solid var(--primary) !important;
        }
        
        .border-left-success {
            border-left: 4px solid var(--success) !important;
        }
        
        .border-left-info {
            border-left: 4px solid var(--info) !important;
        }
        
        .border-left-warning {
            border-left: 4px solid var(--warning) !important;
        }
        
        .border-left-danger {
            border-left: 4px solid var(--danger) !important;
        }
        
        /* Text utilities */
        .text-xs {
            font-size: 0.7rem;
        }
        
        .text-primary {
            color: var(--primary) !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        /* Table styles */
        .table-responsive {
            overflow-x: auto;
        }
        
        .table th {
            font-weight: 600;
            background-color: #f8f9fc;
        }
        
        /* Chart area */
        .chart-area {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Toggle sidebar on mobile */
        #sidebarToggle {
            background-color: transparent;
            color: rgba(255, 255, 255, 0.8);
            border: none;
        }
        
        #sidebarToggle:hover {
            color: #fff;
        }
        
        .sidebar-toggled .sidebar {
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-toggled .main-content, .sidebar-toggled .topbar {
            margin-left: 0;
            left: 0;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 767.98px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .main-content, .topbar {
                margin-left: 0;
                left: 0;
            }
            
            .sidebar.toggled {
                width: var(--sidebar-width);
            }
            
            .sidebar.toggled ~ .main-content,
            .sidebar.toggled ~ .topbar {
                margin-left: 0;
                left: 0;
            }
            
            .chart-area {
                height: 250px;
            }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow fixed-top">
        <!-- Sidebar Toggle (Topbar) -->
        <button id="sidebarToggle" class="btn d-md-none rounded-circle me-3">
            <i class="fa fa-bars"></i>
        </button>
        
        <!-- Topbar Branding -->
        <a class="navbar-brand d-none d-md-block fw-bold text-primary" href="../../../index.php" target="_blank">
            <i class="fas fa-book-open me-2"></i>BookHub
        </a>
        
        <!-- Topbar Navbar -->
        <ul class="navbar-nav ms-auto">
            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
                <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-search fa-fw"></i>
                </a>
                <!-- Dropdown - Messages -->
                <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                    <form class="form-inline me-auto w-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </li>

            <!-- Nav Item - Alerts -->
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <!-- Counter - Alerts -->
                    <span class="badge bg-danger badge-counter">3+</span>
                </a>
                <!-- Dropdown - Alerts -->
                <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown">
                    <h6 class="dropdown-header bg-primary text-white">
                        Alerts Center
                    </h6>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-file-alt text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">December 12, 2023</div>
                            <span class="fw-bold">A new order has been placed!</span>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-success">
                                <i class="fas fa-check text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">December 7, 2023</div>
                            Your book "PHP & MySQL for Beginners" has been approved!
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">December 2, 2023</div>
                            "Web Development Essentials" is low in stock (2 remaining)
                        </div>
                    </a>
                    <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                </div>
            </li>

            <!-- Nav Item - Messages -->
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-envelope fa-fw"></i>
                    <!-- Counter - Messages -->
                    <span class="badge bg-danger badge-counter">7</span>
                </a>
                <!-- Dropdown - Messages -->
                <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="messagesDropdown">
                    <h6 class="dropdown-header bg-primary text-white">
                        Message Center
                    </h6>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="dropdown-list-image me-3">
                            <img class="rounded-circle" src="../assets/images/default-profile.jpg" alt="Customer">
                            <div class="status-indicator bg-success"></div>
                        </div>
                        <div>
                            <div class="text-truncate">When will "Advanced Python" be back in stock?</div>
                            <div class="small text-gray-500">Emily Fowler · 58m</div>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <div class="dropdown-list-image me-3">
                            <img class="rounded-circle" src="../assets/images/default-profile.jpg" alt="Customer">
                            <div class="status-indicator"></div>
                        </div>
                        <div>
                            <div class="text-truncate">Can I get a discount for bulk ordering?</div>
                            <div class="small text-gray-500">David Morgan · 2d</div>
                        </div>
                    </a>
                    <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                </div>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['username']; ?></span>
                    <img class="img-profile rounded-circle" src="../assets/images/default-profile.jpg" width="32" height="32">
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                        Profile
                    </a>
                    <a class="dropdown-item" href="settings.php">
                        <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                        Settings
                    </a>
                    <a class="dropdown-item" href="sales.php">
                        <i class="fas fa-list fa-sm fa-fw me-2 text-gray-400"></i>
                        Sales Report
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </nav>