<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/auth/login.php');
    exit;
}

require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../models/Order.php';

$database = new Database();
$db = $database->connect();
$orderModel = new Order($db);

// Handle order status update
if(isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    $orderModel->updateOrderStatus($orderId, $status);
    header('Location: /views/admin/orders.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Get orders with pagination and filters
$orders = $orderModel->getOrders($offset, $recordsPerPage, $search, $statusFilter, $dateFrom, $dateTo);
$totalRecords = $orderModel->getTotalOrders($search, $statusFilter, $dateFrom, $dateTo);
$totalPages = ceil($totalRecords / $recordsPerPage);

require_once __DIR__ . '/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Order Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.print();">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <a href="/views/admin/export-orders.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download me-1"></i> Export
                    </a>
                </div>
            </div>

            <!-- Search and Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by order ID or customer" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?php echo $dateFrom; ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" placeholder="To Date" value="<?php echo $dateTo; ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                            <a href="/views/admin/orders.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="me-3">
                                    <div class="text-white-75">Total Orders</div>
                                    <div class="text-lg fw-bold"><?php echo $orderModel->countOrdersByStatus(); ?></div>
                                </div>
                                <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="me-3">
                                    <div class="text-white-75">Pending Orders</div>
                                    <div class="text-lg fw-bold"><?php echo $orderModel->countOrdersByStatus('pending'); ?></div>
                                </div>
                                <i class="fas fa-clock fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="me-3">
                                    <div class="text-white-75">Completed Orders</div>
                                    <div class="text-lg fw-bold"><?php echo $orderModel->countOrdersByStatus('completed'); ?></div>
                                </div>
                                <i class="fas fa-check-circle fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="me-3">
                                    <div class="text-white-75">Cancelled Orders</div>
                                    <div class="text-lg fw-bold"><?php echo $orderModel->countOrdersByStatus('cancelled'); ?></div>
                                </div>
                                <i class="fas fa-ban fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shopping-cart me-1"></i> Orders
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($orders && count($orders) > 0): ?>
                                    <?php foreach($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['customer_name']; ?></td>
                                            <td><?php echo $order['item_count']; ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td>
                                                <?php if($order['status'] == 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php elseif($order['status'] == 'processing'): ?>
                                                    <span class="badge bg-primary">Processing</span>
                                                <?php elseif($order['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif($order['status'] == 'cancelled'): ?>
                                                    <span class="badge bg-danger">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/views/admin/order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $order['id']; ?>" title="Update Status">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="/views/admin/print-invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary" title="Print Invoice">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </div>
                                                
                                                <!-- Status Update Modal -->
                                                <div class="modal fade" id="updateStatusModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="updateStatusModalLabel<?php echo $order['id']; ?>">Update Order Status</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="status<?php echo $order['id']; ?>" class="form-label">Status</label>
                                                                        <select class="form-select" id="status<?php echo $order['id']; ?>" name="status" required>
                                                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&status=<?php echo $statusFilter; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&status=<?php echo $statusFilter; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&status=<?php echo $statusFilter; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once __DIR__ . '/admin-footer.php';
?>