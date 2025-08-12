<?php
// includes/seller-sidebar.php

// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <div class="text-center mb-4">
            <img src="../assets/images/logo-light.png" alt="BookHub" class="img-fluid px-3" style="max-width: 180px;">
            <div class="text-white mt-2">Seller Center</div>
        </div>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Management</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="../dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (in_array($current_page, ['books.php', 'add-book.php', 'edit-book.php'])) ? 'active' : ''; ?>" href="../books.php">
                    <i class="fas fa-fw fa-book"></i>
                    Books
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (in_array($current_page, ['orders.php', 'order-details.php'])) ? 'active' : ''; ?>" href="../orders.php">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    Orders
                    <?php if(isset($pending_orders) && $pending_orders > 0): ?>
                    <span class="badge bg-warning text-dark ms-1"><?php echo $pending_orders; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'inventory.php') ? 'active' : ''; ?>" href="../inventory.php">
                    <i class="fas fa-fw fa-boxes"></i>
                    Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'sales.php') ? 'active' : ''; ?>" href="../sales.php">
                    <i class="fas fa-fw fa-chart-line"></i>
                    Sales Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reviews.php') ? 'active' : ''; ?>" href="../reviews.php">
                    <i class="fas fa-fw fa-star"></i>
                    Reviews
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Account</span>
        </h6>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="../profile.php">
                    <i class="fas fa-fw fa-user"></i>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="../settings.php">
                    <i class="fas fa-fw fa-cog"></i>
                    Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../auth/logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
            <span>Help</span>
        </h6>
        
        <ul class="nav flex-column mb-5">
            <li class="nav-item">
                <a class="nav-link" href="help.php">
                    <i class="fas fa-fw fa-question-circle"></i>
                    Help Center
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../contact.php" target="_blank">
                    <i class="fas fa-fw fa-envelope"></i>
                    Contact Support
                </a>
            </li>
        </ul>
        
        <div class="px-3 mt-auto mb-3 text-center">
            <div class="text-white-50 small">
                <span>© <?php echo date('Y'); ?> BookHub Inc.</span>
            </div>
        </div>
    </div>
</nav>