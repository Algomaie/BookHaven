<?php
// views/seller/sales.php
session_start();

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: /login.php');
    exit;
}

// Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/Order.php';
require_once '../../models/Book.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$orderModel = new Order($db);
$bookModel = new Book($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Get sales statistics
$total_sales = $orderModel->getSellerTotalSales($seller_id);
$total_orders = $orderModel->getTotalSellerOrders($seller_id);
$avg_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;

// Get top selling books
$top_books = $bookModel->getTopSellingBooks(5);

// Get monthly sales data (last 12 months)
$monthly_sales = [];
$months = [];

for ($i = 11; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    // Get sales for this month
    $month_sales = $orderModel->getSellerMonthlySales($seller_id, $month, $year);
    
    $months[] = $month_name;
    $monthly_sales[] = $month_sales;
}

// Get sales by category
$category_sales = $orderModel->getSellerCategorySales($seller_id);

// Include seller header
include_once 'includes/seller-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Seller Sidebar -->
        <?php include_once 'includes/seller-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Sales Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="printReport()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportCSV()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-calendar me-1"></i> This Year
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="timeRangeDropdown">
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item active" href="#">This Year</a></li>
                            <li><a class="dropdown-item" href="#">All Time</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Custom Range</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Sales Overview Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_sales, 2); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Average Order Value</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($avg_order_value, 2); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calculator fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Conversion Rate</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">3.5%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-percent fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sales Charts Row -->
            <div class="row">
                <!-- Monthly Sales Chart -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Monthly Sales (Last 12 Months)</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                     aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Chart Options:</div>
                                    <a class="dropdown-item" href="#">View as Bar Chart</a>
                                    <a class="dropdown-item" href="#">View as Line Chart</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Export Chart</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="monthlySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Sales Chart -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Sales by Category</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                     aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Chart Options:</div>
                                    <a class="dropdown-item" href="#">View as Pie Chart</a>
                                    <a class="dropdown-item" href="#">View as Bar Chart</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Export Chart</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="categorySalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Selling Books & Sales Details Row -->
            <div class="row">
                <!-- Top Selling Books -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Top Selling Books</h6>
                        </div>
                        <div class="card-body">
                            <?php if(empty($top_books)): ?>
                                <div class="text-center py-4">
                                    <img src="../assets/images/no-data.svg" alt="No Sales Data" style="max-width: 120px;" class="img-fluid mb-3">
                                    <h5>No sales data available</h5>
                                    <p class="text-muted">Your top selling books will appear here once you make some sales.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Book</th>
                                                <th>Units Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($top_books as $book): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="../uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                                                 class="img-thumbnail me-2" style="width: 40px; height: 60px; object-fit: cover;">
                                                            <div>
                                                                <div class="fw-bold"><?php echo htmlspecialchars($book['title']); ?></div>
                                                                <div class="small text-muted">By <?php echo htmlspecialchars($book['author']); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?php echo $book['quantity_sold']; ?></td>
                                                    <td>$<?php echo number_format($book['revenue'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sales Details -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sales Analytics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card border-left-success h-100 py-2">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                This Month</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                $<?php echo number_format($monthly_sales[count($monthly_sales) - 1], 2); ?>
                                            </div>
                                            <?php
                                            // Calculate month-over-month growth
                                            $current_month = $monthly_sales[count($monthly_sales) - 1];
                                            $prev_month = $monthly_sales[count($monthly_sales) - 2];
                                            $growth = 0;
                                            if($prev_month > 0) {
                                                $growth = (($current_month - $prev_month) / $prev_month) * 100;
                                            }
                                            ?>
                                            <div class="mt-2 small">
                                                <?php if($growth > 0): ?>
                                                    <span class="text-success"><i class="fas fa-arrow-up me-1"></i><?php echo number_format($growth, 1); ?>%</span>
                                                    <span class="text-muted">from last month</span>
                                                <?php elseif($growth < 0): ?>
                                                    <span class="text-danger"><i class="fas fa-arrow-down me-1"></i><?php echo number_format(abs($growth), 1); ?>%</span>
                                                    <span class="text-muted">from last month</span>
                                                <?php else: ?>
                                                    <span class="text-muted">No change from last month</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="card border-left-info h-100 py-2">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Revenue Target</div>
                                            <?php
                                            // Calculate percentage of monthly target
                                            $monthly_target = 5000; // Example target
                                            $current_month = $monthly_sales[count($monthly_sales) - 1];
                                            $target_percent = min(100, ($current_month / $monthly_target) * 100);
                                            ?>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 me-3 font-weight-bold text-gray-800">
                                                        <?php echo number_format($target_percent, 1); ?>%
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                             style="width: <?php echo $target_percent; ?>%"
                                                             aria-valuenow="<?php echo $target_percent; ?>" aria-valuemin="0"
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 small">
                                                <span class="text-muted">$<?php echo number_format($current_month, 2); ?> of $<?php echo number_format($monthly_target, 2); ?> target</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h6 class="font-weight-bold mb-3">Sales Distribution</h6>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 45%;" 
                                     aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                                    Fiction (45%)
                                </div>
                                <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" 
                                     aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                    Non-Fiction (25%)
                                </div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: 15%;" 
                                     aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                                    Kids (15%)
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 15%;" 
                                     aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                                    Others (15%)
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6 class="font-weight-bold mb-3">Sales Summary</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <span class="text-muted">Best Selling Category:</span>
                                    <span class="fw-bold">Fiction</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <span class="text-muted">Best Selling Book:</span>
                                    <span class="fw-bold"><?php echo !empty($top_books) ? htmlspecialchars($top_books[0]['title']) : 'N/A'; ?></span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <span class="text-muted">Peak Sales Day:</span>
                                    <span class="fw-bold">Saturday</span>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <span class="text-muted">Peak Sales Time:</span>
                                    <span class="fw-bold">2:00 PM - 5:00 PM</span>
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

<!-- Sales Charts -->
<script>
    // Sales data from PHP
    var months = <?php echo json_encode($months); ?>;
    var monthlySales = <?php echo json_encode($monthly_sales); ?>;
    
    // Category data
    var categories = ["Fiction", "Non-Fiction", "Kids", "Academic", "Others"];
    var categorySales = [45, 25, 15, 10, 5]; // Example data
    
    // Monthly Sales Chart
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('monthlySalesChart').getContext('2d');
        var monthlySalesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Sales ($)',
                    data: monthlySales,
                    backgroundColor: 'rgba(93, 92, 222, 0.2)',
                    borderColor: '#5D5CDE',
                    tension: 0.3,
                    pointBorderWidth: 3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
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
                }
            }
        });
        
        // Category Sales Chart
        var ctxPie = document.getElementById('categorySalesChart').getContext('2d');
        var categorySalesChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: categories,
                datasets: [{
                    data: categorySales,
                    backgroundColor: [
                        '#5D5CDE',
                        '#4e73df',
                        '#36b9cc',
                        '#1cc88a',
                        '#f6c23e'
                    ],
                    hoverBackgroundColor: [
                        '#4b4aa5',
                        '#2e59d9',
                        '#2c9faf',
                        '#17a673',
                        '#dda20a'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
    });
    
    // Print report function
    function printReport() {
        window.print();
    }
    
    // Export CSV function
    function exportCSV() {
        // Create CSV content
        let csvContent = "Month,Sales\n";
        
        // Add data rows
        for (let i = 0; i < months.length; i++) {
            csvContent += months[i] + "," + monthlySales[i] + "\n";
        }
        
        // Create and trigger download
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "sales_report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php include_once 'includes/seller-footer.php'; ?>