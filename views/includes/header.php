<?php
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Online Book Selling Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>../../../../index.phpp">
                <i class="fas fa-book-open me-2"></i><?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>../../../../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>../../../../books.php">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>../../../../categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>../../../../contact.php">Contact</a>
                    </li>
                </ul>
                <form class="d-flex me-3" action="<?php echo BASE_URL; ?>search.php" method="GET">
                    <input class="form-control me-2" type="search" name="keywords" placeholder="Search books..." aria-label="Search">
                    <button class="btn btn-light" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/admin/dashboard.php">Admin Dashboard</a></li>
                                <?php elseif($_SESSION['role'] === 'seller'): ?>
                                
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/seller/dashboard.php">Seller Dashboard</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/profile.php">My Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/orders.php">My Orders</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>views/auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>views/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>views/auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-count" class="badge bg-danger rounded-pill">
                                <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
