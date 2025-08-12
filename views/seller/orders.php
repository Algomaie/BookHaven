<?php
// views/seller/orders.php
// Start session
session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: ../auth/login.php?redirect=seller/orders.php');
    exit();
}

// Include config files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/Book.php';
require_once '../../models/Order.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$orderModel = new Order($db);
$bookModel = new Book($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Get filter parameters
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;

// Prepare filter parameters
$filters = [
    'seller_id' => $seller_id,
    'status' => $status,
    'search' => $search,
    'date_from' => $date_from,
    'date_to' => $date_to,
    'sort' => $sort,
    'page' => $page,
    'limit' => $limit
];

// Get orders
$orders = $orderModel->getFilteredOrdersBySeller($filters);
$totalOrders = $orderModel->getTotalFilteredOrdersBySeller($filters);

// Calculate total pages
$totalPages = ceil($totalOrders / $limit);

// Get pending orders count for badge
$pendingFilters = [
    'seller_id' => $seller_id,
    'status' => 'pending'
];
$pending_orders = $orderModel->getTotalFilteredOrdersBySeller($pendingFilters);

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
                <h1 class="h2">Manage Orders</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="refreshOrders">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalOrders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shopping-bag fa-2x text-gray-300"></i>
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
                                        Pending Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Shipped Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                            $shippedFilters = [
                                                'seller_id' => $seller_id,
                                                'status' => 'shipped'
                                            ];
                                            echo $orderModel->getTotalFilteredOrdersBySeller($shippedFilters);
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
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
                                        Completed Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php
                                            $completedFilters = [
                                                'seller_id' => $seller_id,
                                                'status' => 'delivered'
                                            ];
                                            echo $orderModel->getTotalFilteredOrdersBySeller($completedFilters);
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Filter Orders</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Order #, Customer name...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="total_high" <?php echo $sort === 'total_high' ? 'selected' : ''; ?>>Amount (High to Low)</option>
                                <option value="total_low" <?php echo $sort === 'total_low' ? 'selected' : ''; ?>>Amount (Low to High)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Orders List</h6>
                    <span>
                        Showing <?php echo ($totalOrders > 0) ? (($page - 1) * $limit) + 1 : 0; ?> to 
                        <?php echo min($page * $limit, $totalOrders); ?> of 
                        <?php echo $totalOrders; ?> orders
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-orders.svg" alt="No Orders" style="max-width: 200px;" class="mb-3">
                            <h4>No orders found</h4>
                            <p class="text-muted">Try changing your search criteria or check back later.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="text-primary fw-bold">#<?php echo $order['order_number']; ?></a></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?><br>
                                                <span class="text-muted small"><?php echo date('g:i A', strtotime($order['created_at'])); ?></span></td>
                                            <td>
                                                <?php echo $order['customer_name']; ?><br>
                                                <span class="text-muted small"><?php echo $order['customer_email']; ?></span>
                                            </td>
                                            <td>
                                                <?php echo $order['item_count']; ?> items<br>
                                                <span class="text-muted small"><?php echo $order['book_titles']; ?></span>
                                            </td>
                                            <td>$<?php echo number_format($order['seller_total'], 2); ?></td>
                                            <td>
                                                <?php if ($order['order_status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                                                <?php elseif ($order['order_status'] == 'processing'): ?>
                                                    <span class="badge bg-primary px-3 py-2">Processing</span>
                                                <?php elseif ($order['order_status'] == 'shipped'): ?>
                                                    <span class="badge bg-info text-dark px-3 py-2">Shipped</span>
                                                <?php elseif ($order['order_status'] == 'delivered'): ?>
                                                    <span class="badge bg-success px-3 py-2">Delivered</span>
                                                <?php elseif ($order['order_status'] == 'cancelled'): ?>
                                                    <span class="badge bg-danger px-3 py-2">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                    <?php if ($order['order_status'] == 'pending'): ?>
                                                        <a href="process-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check me-1"></i> Process
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Orders pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=1&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort=<?php echo $sort; ?>">First</a>
                                    </li>
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort=<?php echo $sort; ?>">Previous</a>
                                    </li>
                                    
                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort=<?php echo $sort; ?>">Next</a>
                                    </li>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $totalPages; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort=<?php echo $sort; ?>">Last</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Export functionality -->
<script>
    // Export table to CSV
    function exportToCSV() {
        const table = document.getElementById('ordersTable');
        if (!table) return;
        
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Skip the actions column
                if (j !== 6) {
                    // Clean the text content (remove extra spaces, newlines, etc)
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').trim();
                    data = data.replace(/\s{2,}/g, ' '); // Replace multiple spaces with single space
                    
                    // Escape quotes and wrap in quotes
                    data = '"' + data.replace(/"/g, '""') + '"';
                    row.push(data);
                }
            }
            
            csv.push(row.join(','));
        }
        
        const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'orders_export_<?php echo date('Y-m-d'); ?>.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh orders button
        const refreshButton = document.getElementById('refreshOrders');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
                this.disabled = true;
                window.location.reload();
            });
        }
        
        // Date range validation
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        
        if (dateFrom && dateTo) {
            dateFrom.addEventListener('change', function() {
                if (dateTo.value && this.value > dateTo.value) {
                    alert('From date cannot be later than To date.');
                    this.value = dateTo.value;
                }
            });
            
            dateTo.addEventListener('change', function() {
                if (dateFrom.value && this.value < dateFrom.value) {
                    alert('To date cannot be earlier than From date.');
                    this.value = dateFrom.value;
                }
            });
        }
    });
</script>

<?php include_once 'includes/seller-footer.php'; ?>