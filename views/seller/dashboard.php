<?php
// views/seller/dashboard.php
// Start session
session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../auth/login.php?redirect=seller/dashboard.php');
    exit();
}

// Include config files
// Include required models
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Review.php';

require_once '../../models/Book.php';
require_once '../../models/Category.php';

require_once '../../models/Order.php';
// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$bookModel = new Book($db); 
$orderModel = new Order($db);
$userModel = new User($db);
$reviewModel = new Review($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Get statistics
$bookModel->seller_id = $seller_id;
$totalBooks = $bookModel->countBooksBySeller($seller_id);
// $pendingApprovals = $bookModel->countPendingApprovals();
// $outOfStock = $bookModel->countOutOfStock();

// Get recent orders
$filters = [
    'seller_id' => $seller_id,
    'limit' => 5
];
//$recentOrders = $orderModel->getFilteredOrdersBySeller($filters);

// Get monthly sales data for chart
$monthlySales = $orderModel->getSellerMonthlySales($seller_id);

// Get top selling books
$topSellingBooks = $bookModel->getTopSellingBooks($seller_id, 5);

// Get recent reviews
$recentReviews = $reviewModel->getRecentReviewsBySeller($seller_id, 5);

// Include seller header
include_once 'includes/seller-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Seller Sidebar -->
        <?php include_once 'includes/seller-sidebar.php';?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2">Dashboard</h1>
                    <p class="text-muted">Welcome back, <?php echo $_SESSION['username']; ?>!</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="refreshData">
                        <i class="fas fa-sync-alt me-1"></i> Refresh Data
                    </button>
                </div>
            </div>
            
            <!-- Quick Stats Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Books</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalBooks; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Monthly Revenue</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($orderModel->getCurrentMonthRevenue($seller_id), 2); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Approvals</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingApprovals; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Out of Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $outOfStock; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row">
                <!-- Monthly Revenue Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">View Options:</div>
                                    <a class="dropdown-item chart-period" href="#" data-period="6">Last 6 Months</a>
                                    <a class="dropdown-item chart-period" href="#" data-period="12">Last Year</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="sales.php">View Detailed Report</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales by Category Pie Chart -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Sales by Category</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">View Options:</div>
                                    <a class="dropdown-item" href="#">By Revenue</a>
                                    <a class="dropdown-item" href="#">By Units Sold</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="sales.php">View Detailed Report</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="categorySalesChart"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                <span class="me-2">
                                    <i class="fas fa-circle text-primary"></i> Fiction
                                </span>
                                <span class="me-2">
                                    <i class="fas fa-circle text-success"></i> Non-Fiction
                                </span>
                                <span class="me-2">
                                    <i class="fas fa-circle text-info"></i> Academic
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row">
                <!-- Recent Orders -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recentOrders)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="mb-0">No orders yet!</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order #</th>
                                                <th>Date</th>
                                                <th>Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="text-primary fw-bold">#<?php echo $order['order_number']; ?></a></td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                    <td><?php echo $order['customer_name']; ?></td>
                                                    <td>$<?php echo number_format($order['seller_total'], 2); ?></td>
                                                    <td>
                                                        <?php if ($order['order_status'] == 'pending'): ?>
                                                            <span class="badge bg-warning text-dark">Pending</span>
                                                        <?php elseif ($order['order_status'] == 'processing'): ?>
                                                            <span class="badge bg-primary">Processing</span>
                                                        <?php elseif ($order['order_status'] == 'shipped'): ?>
                                                            <span class="badge bg-info text-dark">Shipped</span>
                                                        <?php elseif ($order['order_status'] == 'delivered'): ?>
                                                            <span class="badge bg-success">Delivered</span>
                                                        <?php elseif ($order['order_status'] == 'cancelled'): ?>
                                                            <span class="badge bg-danger">Cancelled</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="orders.php" class="text-primary">View All Orders <i class="fas fa-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Top Selling Books -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Top Selling Books</h6>
                            <a href="books.php?sort=bestselling" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($topSellingBooks)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <p class="mb-0">No sales data available yet!</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($topSellingBooks as $index => $book): ?>
                                        <a href="book-details.php?id=<?php echo $book['id']; ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 align-items-center">
                                                <span class="badge bg-primary rounded-circle me-3"><?php echo $index + 1; ?></span>
                                                <img src="../uploads/book_covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" class="img-thumbnail me-3" style="width: 40px;">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?php echo $book['title']; ?></h6>
                                                    <p class="mb-0 text-muted small">by <?php echo $book['author']; ?></p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="fw-bold"><?php echo $book['copies_sold']; ?> copies</span><br>
                                                    <span class="text-success">$<?php echo number_format($book['total_revenue'], 2); ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="books.php?sort=bestselling" class="text-primary">View All Books <i class="fas fa-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Row -->
            <div class="row">
                <!-- Low Stock Alert -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4 border-left-warning">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-warning">Low Stock Alert</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php
                                $lowStockBooks = $bookModel->getLowStockBooks(5);
                                if (empty($lowStockBooks)):
                            ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="mb-0">All your books have sufficient stock!</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($lowStockBooks as $book): ?>
                                        <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 align-items-center">
                                                <img src="../uploads/book_covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>" class="img-thumbnail me-3" style="width: 40px;">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?php echo $book['title']; ?></h6>
                                                    <p class="mb-0 text-muted small">by <?php echo $book['author']; ?></p>
                                                </div>
                                                <div class="text-end">
                                                    <?php if ($book['stock_quantity'] <= 0): ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Low Stock: <?php echo $book['stock_quantity']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="update-stock.php" class="text-warning">Update Stock Levels <i class="fas fa-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reviews -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Reviews</h6>
                            <a href="reviews.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recentReviews)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                    <p class="mb-0">No reviews yet!</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentReviews as $review): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0"><?php echo $review['username']; ?></h6>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-1 small"><a href="book-details.php?id=<?php echo $review['book_id']; ?>" class="text-primary"><?php echo $review['book_title']; ?></a></p>
                                            <div class="d-flex justify-content-between align-items-center">
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
                                                
                                                <?php if ($review['is_verified_purchase']): ?>
                                                    <span class="badge bg-success">Verified Purchase</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mt-1 mb-0"><?php echo substr(htmlspecialchars($review['review_text']), 0, 100) . (strlen($review['review_text']) > 100 ? '...' : ''); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="reviews.php" class="text-primary">View All Reviews <i class="fas fa-chevron-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Alerts & Notifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Alerts & Notifications</h6>
                </div>
                <div class="card-body">
                    <?php
                        // Display alerts based on system state
                        $hasAlerts = false;
                        
                        // Pending approvals alert
                        if ($pendingApprovals > 0):
                            $hasAlerts = true;
                    ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Pending Approvals:</strong> You have <?php echo $pendingApprovals; ?> book<?php echo $pendingApprovals > 1 ? 's' : ''; ?> waiting for approval.
                            <a href="books.php?status=pending" class="alert-link">View pending books</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                        // Out of stock alert
                        if ($outOfStock > 0):
                            $hasAlerts = true;
                    ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Out of Stock:</strong> You have <?php echo $outOfStock; ?> book<?php echo $outOfStock > 1 ? 's' : ''; ?> that are out of stock.
                            <a href="books.php?stock=0" class="alert-link">Update inventory</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                        // Pending orders alert
                        $pendingOrders = $orderModel->countPendingOrdersBySeller($seller_id);
                        if ($pendingOrders > 0):
                            $hasAlerts = true;
                    ?>
                        <div class="alert alert-info">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <strong>New Orders:</strong> You have <?php echo $pendingOrders; ?> new order<?php echo $pendingOrders > 1 ? 's' : ''; ?> to process.
                            <a href="orders.php?status=pending" class="alert-link">Process orders</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                        // New reviews alert
                        $newReviews = $reviewModel->countNewReviewsBySeller($seller_id);
                        if ($newReviews > 0):
                            $hasAlerts = true;
                    ?>
                        <div class="alert alert-success">
                            <i class="fas fa-star me-2"></i>
                            <strong>New Reviews:</strong> You have received <?php echo $newReviews; ?> new review<?php echo $newReviews > 1 ? 's' : ''; ?>.
                            <a href="reviews.php" class="alert-link">View reviews</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$hasAlerts): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>All Caught Up!</strong> You don't have any pending notifications at the moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dashboard Charts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Revenue Chart
        var ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Sample data - in a real application, this would be fetched from the server
        var monthlyRevenue = <?php echo json_encode(array_column($monthlySales, 'revenue')); ?>;
        var months = <?php echo json_encode(array_column($monthlySales, 'month')); ?>;
        
        var revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue ($)',
                    data: monthlyRevenue,
                    backgroundColor: 'rgba(93, 92, 222, 0.2)',
                    borderColor: '#5D5CDE',
                    tension: 0.3,
                    pointBorderWidth: 3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        // Sales by Category Pie Chart
        var pieCtx = document.getElementById('categorySalesChart').getContext('2d');
        var categorySalesChart = new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Fiction', 'Non-Fiction', 'Academic', 'Children', 'Other'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: ['#5D5CDE', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#4e4dc7', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                    hoverBorderColor: 'rgba(234, 236, 244, 1)',
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '60%',
            }
        });
        
        // Refresh data button functionality
        document.getElementById('refreshData').addEventListener('click', function() {
            const button = this;
            const originalHTML = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
            button.disabled = true;
            
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        });
        
        // Chart period selection
        const chartPeriodLinks = document.querySelectorAll('.chart-period');
        chartPeriodLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const period = parseInt(this.getAttribute('data-period'));
                
                // In a real application, you would fetch new data from the server
                // For this example, we'll just update the chart with fewer months
                
                if (period === 6) {
                    revenueChart.data.labels = months.slice(-6);
                    revenueChart.data.datasets[0].data = monthlyRevenue.slice(-6);
                } else {
                    revenueChart.data.labels = months;
                    revenueChart.data.datasets[0].data = monthlyRevenue;
                }
                
                revenueChart.update();
            });
        });
    });
    
    // Export to PDF functionality
    function exportToPDF() {
        alert('This would export a PDF report of the dashboard in a real implementation.');
    }
</script>

<?php include_once 'includes/seller-footer.php'; ?>