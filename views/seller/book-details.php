<?php
// views/seller/book-details.php
// Start session
session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: ../login.php?redirect=seller/book-details.php');
    exit();
}

// Include config files
require_once '../config/config.php';
require_once '../config/database.php';

// Include required models
require_once '../models/Book.php';
require_once '../models/Category.php';
require_once '../models/Review.php';
require_once '../models/Order.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$bookModel = new Book($db);
$categoryModel = new Category($db);
$reviewModel = new Review($db);
$orderModel = new Order($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: books.php');
    exit();
}

$book_id = intval($_GET['id']);

// Get book details
$bookModel->id = $book_id;
$bookModel->seller_id = $seller_id; // Ensure only the seller's own books can be viewed
$book = $bookModel->getBookDetails();

// If book not found or doesn't belong to the seller, redirect
if (!$book || $book['seller_id'] != $seller_id) {
    header('Location: books.php');
    exit();
}

// Get book category
$categoryModel->id = $book['category_id'];
$category = $categoryModel->getCategory();

// Get book reviews
$reviews = $reviewModel->getBookReviews($book_id);

// Get average rating
$average_rating = $reviewModel->getAverageRating($book_id);

// Get book sales information
$total_sales = 0;
$total_revenue = 0;
$orders_count = 0;

// Include seller header
include_once 'includes/seller-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Seller Sidebar -->
        <?php include_once 'includes/seller-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2"><?php echo $book['title']; ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="books.php">Books</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Book Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="edit-book.php?id=<?php echo $book_id; ?>" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-edit me-1"></i> Edit Book
                    </a>
                    <a href="../book-details.php?id=<?php echo $book_id; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-1"></i> View on Site
                    </a>
                </div>
            </div>
            
            <!-- Book Details Card -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-0 text-center">
                            <img src="../uploads/book_covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" class="img-fluid book-cover">
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-grid gap-2">
                                <a href="edit-book.php?id=<?php echo $book_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Edit Book
                                </a>
                                <a href="update-stock.php?id=<?php echo $book_id; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-boxes me-1"></i> Update Stock
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Book Information</h5>
                            <div>
                                <span class="badge rounded-pill bg-<?php 
                                    echo $book['approval_status'] === 'approved' ? 'success' : 
                                        ($book['approval_status'] === 'pending' ? 'warning text-dark' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($book['approval_status']); ?>
                                </span>
                                <?php if ($book['is_featured']): ?>
                                    <span class="badge rounded-pill bg-primary ms-1">
                                        <i class="fas fa-star me-1"></i> Featured
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Title:</div>
                                <div class="col-md-9"><?php echo $book['title']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Author:</div>
                                <div class="col-md-9"><?php echo $book['author']; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Category:</div>
                                <div class="col-md-9"><?php echo $category['name']; ?></div>
                            </div>
                            <?php if (!empty($book['isbn'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-3 fw-bold">ISBN:</div>
                                    <div class="col-md-9"><?php echo $book['isbn']; ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Price:</div>
                                <div class="col-md-9">
                                    $<?php echo number_format($book['price'], 2); ?>
                                    <?php if ($book['discount_percent'] > 0): 
                                        $discounted_price = $book['price'] * (1 - $book['discount_percent'] / 100);
                                    ?>
                                        <span class="ms-2 text-danger">
                                            <?php echo $book['discount_percent']; ?>% off - 
                                            Final price: <strong>$<?php echo number_format($discounted_price, 2); ?></strong>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Stock Quantity:</div>
                                <div class="col-md-9">
                                    <?php if ($book['stock_quantity'] <= 0): ?>
                                        <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> Out of Stock</span>
                                    <?php elseif ($book['stock_quantity'] < 5): ?>
                                        <span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Low Stock: <?php echo $book['stock_quantity']; ?> remaining</span>
                                    <?php else: ?>
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> In Stock: <?php echo $book['stock_quantity']; ?> available</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($book['publisher'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-3 fw-bold">Publisher:</div>
                                    <div class="col-md-9"><?php echo $book['publisher']; ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($book['publication_date'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-3 fw-bold">Publication Date:</div>
                                    <div class="col-md-9"><?php echo date('F j, Y', strtotime($book['publication_date'])); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($book['language'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-3 fw-bold">Language:</div>
                                    <div class="col-md-9"><?php echo $book['language']; ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($book['page_count'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-3 fw-bold">Page Count:</div>
                                    <div class="col-md-9"><?php echo $book['page_count']; ?> pages</div>
                                </div>
                            <?php endif; ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Date Added:</div>
                                <div class="col-md-9"><?php echo date('F j, Y', strtotime($book['created_at'])); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Last Updated:</div>
                                <div class="col-md-9"><?php echo date('F j, Y', strtotime($book['updated_at'])); ?></div>
                            </div>
                            <?php if ($book['approval_status'] === 'rejected' && !empty($book['rejection_reason'])): ?>
                                <div class="row mb-3">
                                    <div class="col-md-3 fw-bold text-danger">Rejection Reason:</div>
                                    <div class="col-md-9 text-danger"><?php echo $book['rejection_reason']; ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-3 fw-bold">Description:</div>
                                <div class="col-md-9"><?php echo nl2br(htmlspecialchars($book['description'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-primary h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_sales; ?> copies</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_revenue, 2); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-info h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Rating</div>
                                    <div class="d-flex align-items-center">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo number_format($average_rating, 1); ?></div>
                                        <div class="ml-2">
                                            <?php
                                            $fullStars = floor($average_rating);
                                            $halfStar = $average_rating - $fullStars >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            
                                            for ($i = 0; $i < $fullStars; $i++) {
                                                echo '<i class="fas fa-star text-warning"></i>';
                                            }
                                            
                                            if ($halfStar) {
                                                echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                            }
                                            
                                            for ($i = 0; $i < $emptyStars; $i++) {
                                                echo '<i class="far fa-star text-warning"></i>';
                                            }
                                            ?>
                                            <span class="text-muted">(<?php echo count($reviews); ?>)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-warning h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Page Views</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">235</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-eye fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs for Reviews, Orders, etc. -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-tabs card-header-tabs" id="bookDetailsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="true">
                                Customer Reviews (<?php echo count($reviews); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">
                                Orders (<?php echo $orders_count; ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab" aria-controls="analytics" aria-selected="false">
                                Analytics
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="bookDetailsTabsContent">
                        <!-- Reviews Tab -->
                        <div class="tab-pane fade show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                            <?php if (empty($reviews)): ?>
                                <div class="text-center py-5">
                                    <i class="far fa-star fa-3x text-muted mb-3"></i>
                                    <h4>No Reviews Yet</h4>
                                    <p class="text-muted">There are no customer reviews for this book yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <img src="../assets/images/default-profile.jpg" alt="User" class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $review['username']; ?></h6>
                                                        <div class="small text-muted">
                                                            <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                                            <?php if ($review['is_verified_purchase']): ?>
                                                                <span class="badge bg-success">Verified Purchase</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ratings">
                                                    <?php
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $review['rating']) {
                                                            echo '<i class="fas fa-star text-warning"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star text-warning"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <p class="card-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <?php if ($orders_count === 0): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h4>No Orders Yet</h4>
                                    <p class="text-muted">This book hasn't been purchased yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Customer</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Sample order data -->
                                            <tr>
                                                <td><a href="order-details.php?id=1" class="text-primary">#ORD12345</a></td>
                                                <td>Dec 15, 2023</td>
                                                <td>John Smith</td>
                                                <td>1</td>
                                                <td>$<?php echo number_format($book['price'], 2); ?></td>
                                                <td><span class="badge bg-success">Delivered</span></td>
                                                <td>
                                                    <a href="order-details.php?id=1" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Analytics Tab -->
                        <div class="tab-pane fade" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="m-0 font-weight-bold">Sales Over Time</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-area">
                                                <canvas id="salesChart" height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="m-0 font-weight-bold">Page Views</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-area">
                                                <canvas id="viewsChart" height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold">Customer Demographics</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <h6 class="text-muted">Age Distribution</h6>
                                            <canvas id="ageChart" height="250"></canvas>
                                        </div>
                                        <div class="col-lg-6">
                                            <h6 class="text-muted">Geographic Distribution</h6>
                                            <canvas id="geoChart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Analytics Charts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales',
                    data: [0, 0, 1, 2, 1, 0],
                    backgroundColor: 'rgba(93, 92, 222, 0.2)',
                    borderColor: '#5D5CDE',
                    tension: 0.3,
                    pointBorderWidth: 3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Views Chart
        const viewsCtx = document.getElementById('viewsChart').getContext('2d');
        const viewsChart = new Chart(viewsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Page Views',
                    data: [10, 15, 30, 50, 65, 65],
                    backgroundColor: 'rgba(28, 200, 138, 0.2)',
                    borderColor: '#1cc88a',
                    tension: 0.3,
                    pointBorderWidth: 3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Age Distribution Chart
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        const ageChart = new Chart(ageCtx, {
            type: 'pie',
            data: {
                labels: ['18-24', '25-34', '35-44', '45-54', '55+'],
                datasets: [{
                    data: [15, 30, 25, 20, 10],
                    backgroundColor: [
                        '#5D5CDE', 
                        '#1cc88a', 
                        '#36b9cc', 
                        '#f6c23e', 
                        '#e74a3b'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });
        
        // Geographic Distribution Chart
        const geoCtx = document.getElementById('geoChart').getContext('2d');
        const geoChart = new Chart(geoCtx, {
            type: 'doughnut',
            data: {
                labels: ['North America', 'Europe', 'Asia', 'Australia', 'Other'],
                datasets: [{
                    data: [45, 25, 15, 10, 5],
                    backgroundColor: [
                        '#5D5CDE', 
                        '#1cc88a', 
                        '#36b9cc', 
                        '#f6c23e', 
                        '#e74a3b'
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                maintainAspectRatio: false
            }
        });
    });
</script>

<?php include_once 'includes/seller-footer.php'; ?>