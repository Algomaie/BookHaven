<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <!-- Brand Logo with Book Icon -->
        <a class="navbar-brand fw-bold" href="/">
            <i class="fas fa-book-open me-2"></i>BookHaven
        </a>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Main Navigation -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/" aria-current="page">Home</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/book">Browse Books</a>
                </li>
                
                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                        <?php
                        $categories = [
                            'fiction' => 'Fiction',
                            'non-fiction' => 'Non-Fiction',
                            'sci-fi' => 'Science Fiction',
                            'mystery' => 'Mystery',
                            'biography' => 'Biography',
                            'romance' => 'Romance',
                            'fantasy' => 'Fantasy'
                        ];
                        
                        foreach ($categories as $slug => $name) {
                            echo '<li><a class="dropdown-item" href="/book/category/' . htmlspecialchars($slug) . '">' 
                                . htmlspecialchars($name) . '</a></li>';
                        }
                        ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-bold" href="/book/categories">All Categories</a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/about">About</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="/contact">Contact</a>
                </li>
            </ul>
            
            <!-- Search Form (Desktop) -->
            <form class="d-none d-lg-flex me-3" action="/book/search" method="GET" role="search">
                <div class="input-group">
                    <input type="search" name="query" class="form-control" 
                           placeholder="Search books..." aria-label="Search books" 
                           value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" required>
                    <button class="btn btn-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Right Navigation -->
            <ul class="navbar-nav">
                <!-- Cart with Badge -->
                <li class="nav-item">
                    <a class="nav-link position-relative" href="/cart" aria-label="Shopping cart">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php if (!empty($_SESSION['cart_count'])): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo min((int)$_SESSION['cart_count'], 99); ?>
                                <span class="visually-hidden">items in cart</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- User Dropdown -->
                <?php if (is_logged_in()): ?>
                <li class="nav-item dropdown ms-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="me-2 d-none d-sm-block">
                            <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </div>
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header">My Account</h6></li>
                        <li><a class="dropdown-item" href="/user/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="/user/orders"><i class="fas fa-box-open me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="/user/wishlist"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                        
                        <?php if (is_seller()): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/seller/dashboard"><i class="fas fa-store me-2"></i>Seller Dashboard</a></li>
                        <?php endif; ?>
                        
                        <?php if (is_admin()): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/admin"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                        <?php endif; ?>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <!-- Login/Register Links -->
                <li class="nav-item ms-2">
                    <a class="btn btn-outline-light" href="/auth/login">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </li>
                <li class="nav-item ms-2 d-none d-md-block">
                    <a class="btn btn-light" href="/auth/register">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobile Search (Hidden on desktop) -->
<div class="d-lg-none bg-light py-2 border-bottom">
    <div class="container">
        <form action="/book/search" method="GET" role="search">
            <div class="input-group">
                <input type="search" name="query" class="form-control" 
                       placeholder="Search books..." aria-label="Search books"
                       value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" required>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>