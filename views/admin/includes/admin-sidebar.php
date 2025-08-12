<?php
// views/admin/includes/admin-sidebar.php

// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <div class="text-center mb-4">
            <img src="../assets/images/logo-light.png" alt="BookHaven" class="img-fluid px-3" style="max-width: 180px;">
            <div class="text-white mt-2">Admin Panel</div>
        </div>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Main</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_page, 'book') !== false) ? 'active' : ''; ?>" href="books.php">
                    <i class="fas fa-fw fa-book"></i>
                    Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-fw fa-list"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'orders.php' || $current_page == 'order-details.php') ? 'active' : ''; ?>" href="orders.php">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    Orders
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Users</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'customers.php') ? 'active' : ''; ?>" href="customers.php">
                    <i class="fas fa-fw fa-users"></i>
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'sellers.php') ? 'active' : ''; ?>" href="sellers.php">
                    <i class="fas fa-fw fa-store"></i>
                    Sellers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-fw fa-user-shield"></i>
                    Admin Users
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Content</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reviews.php') ? 'active' : ''; ?>" href="reviews.php">
                    <i class="fas fa-fw fa-star"></i>
                    Reviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'banners.php') ? 'active' : ''; ?>" href="banners.php">
                    <i class="fas fa-fw fa-image"></i>
                    Banners
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'pages.php') ? 'active' : ''; ?>" href="pages.php">
                    <i class="fas fa-fw fa-file-alt"></i>
                    Pages
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>System</span>
        </h6>
        
        <ul class="nav flex-column mb-5">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-fw fa-cogs"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'logs.php') ? 'active' : ''; ?>" href="logs.php">
                    <i class="fas fa-fw fa-clipboard-list"></i>
                    System Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
        
        <div class="px-3 mt-auto mb-3 text-center">
            <div class="text-white-50 small">
                <span>© <?php echo date('Y'); ?> BookHaven Inc.</span>
                <span class="d-block">Version 1.0.0</span>
            </div>
        </div>
    </div>
</nav>