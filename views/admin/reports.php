<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../models/Book.php';
require_once __DIR__ . '/../../models/User.php';

$database = new Database();
$db = $database->connect();
$orderModel = new Order($db);
$bookModel = new Book($db);
$userModel = new User($db);

// Date range filter
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Sales summary
$salesSummary = $orderModel->getSalesSummary($dateFrom, $dateTo);
$monthlySales = $orderModel->getMonthlySales();
$topSellingBooks = $bookModel->getTopSellingBooks(5);
$topCategories = $bookModel->getTopCategories(5);
$recentCustomers = $userModel->getRecentCustomers(5);

require_once __DIR__ . '/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Sales Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/views/admin/export-reports.php" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-download me-1"></i> Export
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                            <a href="/views/admin/reports.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sales Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Sales</h5>
                                    <h2 class="mb-0">$<?php echo number_format($salesSummary['total_sales'], 2); ?></h2>
                                </div>
                                <i class="fas fa-dollar-sign fa-3x text-white-50"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <span>From <?php echo date('M d, Y', strtotime($dateFrom)); ?> to <?php echo date('M d, Y', strtotime($dateTo)); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Orders</h5>
                                    <h2 class="mb-0"><?php echo $salesSummary['total_orders']; ?></h2>
                                </div>
                                <i class="fas fa-shopping-cart fa-3x text-white-50"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <span>Average order: $<?php echo $salesSummary['total_orders'] > 0 ? number_format($salesSummary['total_sales'] / $salesSummary['total_orders'], 2) : '0.00'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Books Sold</h5>
                                    <h2 class="mb-0"><?php echo $salesSummary['items_sold']; ?></h2>
                                </div>
                                <i class="fas fa-book fa-3x text-white-50"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <span>Top category: <?php echo !empty($topCategories) ? $topCategories[0]['name'] : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">New Customers</h5>
                                    <h2 class="mb-0"><?php echo $salesSummary['new_customers']; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x text-white-50"></i>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <span>Total customers: <?php echo $userModel->countCustomers(); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Monthly Sales Chart -->
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-chart-bar me-1"></i> Monthly Sales
                        </div>
                        <div class="card-body">
                            <canvas id="monthlySalesChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Top Selling Books -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-chart-pie me-1"></i> Top Selling Books
                        </div>
                        <div class="card-body">
                            <canvas id="topBooksChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Top Categories -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-tags me-1"></i> Top Categories
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Books Sold</th>
                                            <th>Sales Amount</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($topCategories && count($topCategories) > 0): ?>
                                            <?php foreach($topCategories as $category): ?>
                                                <tr>
                                                    <td><?php echo $category['name']; ?></td>
                                                    <td><?php echo $category['books_sold']; ?></td>
                                                    <td>$<?php echo number_format($category['sales_amount'], 2); ?></td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $category['percentage']; ?>%" aria-valuenow="<?php echo $category['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($category['percentage']); ?>%</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Customers -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user-friends me-1"></i> Recent Customers
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Email</th>
                                            <th>Orders</th>
                                            <th>Spent</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($recentCustomers && count($recentCustomers) > 0): ?>
                                            <?php foreach($recentCustomers as $customer): ?>
                                                <tr>
                                                    <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
                                                    <td><?php echo $customer['email']; ?></td>
                                                    <td><?php echo $customer['order_count']; ?></td>
                                                    <td>$<?php echo number_format($customer['total_spent'], 2); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No recent customers</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Chart data from PHP to JS
    const monthlySalesData = <?php echo json_encode($monthlySales); ?>;
    const topBooksData = <?php echo json_encode($topSellingBooks); ?>;
    
    // Monthly Sales Chart
    const monthlySalesChart = new Chart(
        document.getElementById('monthlySalesChart').getContext('2d'),
        {
            type: 'bar',
            data: {
                labels: monthlySalesData.map(item => item.month),
                datasets: [{
                    label: 'Sales ($)',
                    data: monthlySalesData.map(item => item.total),
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
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
        }
    );
    
    // Top Books Chart
    const topBooksChart = new Chart(
        document.getElementById('topBooksChart').getContext('2d'),
        {
            type: 'pie',
            data: {
                labels: topBooksData.map(book => book.title),
                datasets: [{
                    data: topBooksData.map(book => book.quantity_sold),
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.7)',
                        'rgba(25, 135, 84, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(13, 202, 240, 0.7)'
                    ],
                    borderColor: [
                        'rgba(13, 110, 253, 1)',
                        'rgba(25, 135, 84, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(13, 202, 240, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        }
    );
</script>

<?php
require_once __DIR__ . '/admin-footer.php';
?>