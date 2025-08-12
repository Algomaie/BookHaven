<?php
// views/seller/order-details.php
// Start session
session_start();

// Include header and sidebar
require_once 'includes/seller-header.php';

// Include config files

require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/Order.php';

// Include required files
require_once '../../models/User.php';
require_once '../../models/Book.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$orderModel = new Order($db);
$bookModel = new Book($db);
$userModel = new User($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);

// Get order details
$orderModel->id = $order_id;
$order = $orderModel->getOrder();

// Get order items
$orderItems = $orderModel->getOrderItems();

// Filter order items to show only those belonging to this seller
$sellerOrderItems = array_filter($orderItems, function($item) use ($seller_id) {
    return $item['seller_id'] == $seller_id;
});

// If no order items found for this seller, redirect
if (empty($sellerOrderItems)) {
    header('Location: orders.php');
    exit();
}

// Get customer details
$userModel->id = $order['user_id'];
$customer = $userModel->getUser();

// Handle action on order items
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $action = $_POST['action'];
    
    if ($item_id > 0) {
        // Check if item belongs to this seller
        $itemBelongsToSeller = false;
        foreach ($sellerOrderItems as $item) {
            if ($item['id'] == $item_id && $item['seller_id'] == $seller_id) {
                $itemBelongsToSeller = true;
                break;
            }
        }
        
        if ($itemBelongsToSeller) {
            if ($action == 'accept') {
                // Accept order item
                if ($orderModel->updateOrderItemStatus($item_id, 'accepted')) {
                    $message = [
                        'type' => 'success',
                        'text' => 'Order item has been accepted.'
                    ];
                    
                    // Refresh order items
                    $orderItems = $orderModel->getOrderItems();
                    $sellerOrderItems = array_filter($orderItems, function($item) use ($seller_id) {
                        return $item['seller_id'] == $seller_id;
                    });
                } else {
                    $message = [
                        'type' => 'danger',
                        'text' => 'Failed to accept order item.'
                    ];
                }
            } elseif ($action == 'reject') {
                $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
                
                // Reject order item
                if ($orderModel->updateOrderItemStatus($item_id, 'rejected', $reason)) {
                    $message = [
                        'type' => 'success',
                        'text' => 'Order item has been rejected.'
                    ];
                    
                    // Refresh order items
                    $orderItems = $orderModel->getOrderItems();
                    $sellerOrderItems = array_filter($orderItems, function($item) use ($seller_id) {
                        return $item['seller_id'] == $seller_id;
                    });
                } else {
                    $message = [
                        'type' => 'danger',
                        'text' => 'Failed to reject order item.'
                    ];
                }
            } elseif ($action == 'ship') {
                $tracking_number = isset($_POST['tracking_number']) ? $_POST['tracking_number'] : '';
                
                // Ship order item
                if ($orderModel->updateOrderItemStatus($item_id, 'shipped', '', $tracking_number)) {
                    $message = [
                        'type' => 'success',
                        'text' => 'Order item has been marked as shipped.'
                    ];
                    
                    // Refresh order items
                    $orderItems = $orderModel->getOrderItems();
                    $sellerOrderItems = array_filter($orderItems, function($item) use ($seller_id) {
                        return $item['seller_id'] == $seller_id;
                    });
                } else {
                    $message = [
                        'type' => 'danger',
                        'text' => 'Failed to update shipping status.'
                    ];
                }
            }
        } else {
            $message = [
                'type' => 'danger',
                'text' => 'You are not authorized to perform this action.'
            ];
        }
    }
}

// Calculate total for seller's items
$sellerTotal = 0;
foreach ($sellerOrderItems as $item) {
    $sellerTotal += $item['subtotal'];
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Seller Sidebar -->
        <?php include_once 'includes/seller-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <h1 class="h2">Order #<?php echo $order['order_number']; ?></h1>
                    <p class="text-muted">
                        Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Order Status and Items -->
                <div class="col-lg-8">
                    <!-- Order Status Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Order Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Order Status</h6>
                                    <div class="mb-3">
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
                                    </div>
                                    
                                    <h6>Payment Status</h6>
                                    <div>
                                        <?php if ($order['payment_status'] == 'pending'): ?>
                                            <span class="badge bg-warning text-dark px-3 py-2">Payment Pending</span>
                                        <?php elseif ($order['payment_status'] == 'paid'): ?>
                                            <span class="badge bg-success px-3 py-2">Paid</span>
                                        <?php elseif ($order['payment_status'] == 'failed'): ?>
                                            <span class="badge bg-danger px-3 py-2">Payment Failed</span>
                                        <?php elseif ($order['payment_status'] == 'refunded'): ?>
                                            <span class="badge bg-info text-dark px-3 py-2">Refunded</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Payment Method</h6>
                                    <p>
                                        <?php if ($order['payment_method'] == 'credit_card'): ?>
                                            <i class="fas fa-credit-card me-1"></i> Credit Card
                                        <?php elseif ($order['payment_method'] == 'paypal'): ?>
                                            <i class="fab fa-paypal me-1"></i> PayPal
                                        <?php elseif ($order['payment_method'] == 'bank_transfer'): ?>
                                            <i class="fas fa-university me-1"></i> Bank Transfer
                                        <?php endif; ?>
                                    </p>
                                    
                                    <h6>Order Note</h6>
                                    <p class="mb-0">
                                        <?php if (!empty($order['notes'])): ?>
                                            <?php echo htmlspecialchars($order['notes']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No notes provided</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-shopping-basket me-2"></i>Your Items in This Order</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">Image</th>
                                            <th>Book</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sellerOrderItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <img src="../uploads/book_covers/<?php echo $item['cover_image']; ?>" class="img-thumbnail" alt="<?php echo $item['title']; ?>" style="width: 50px;">
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <a href="../book-details.php?id=<?php echo $item['book_id']; ?>" class="text-primary fw-bold">
                                                            <?php echo $item['title']; ?>
                                                        </a>
                                                        <span class="text-muted small">By: <?php echo $item['author']; ?></span>
                                                    </div>
                                                </td>
                                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                                <td>
                                                    <?php if ($item['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php elseif ($item['status'] == 'accepted'): ?>
                                                        <span class="badge bg-primary">Accepted</span>
                                                    <?php elseif ($item['status'] == 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php elseif ($item['status'] == 'shipped'): ?>
                                                        <span class="badge bg-info text-dark">Shipped</span>
                                                    <?php elseif ($item['status'] == 'delivered'): ?>
                                                        <span class="badge bg-success">Delivered</span>
                                                    <?php elseif ($item['status'] == 'returned'): ?>
                                                        <span class="badge bg-secondary">Returned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['status'] == 'pending'): ?>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#acceptModal" data-item-id="<?php echo $item['id']; ?>" data-item-title="<?php echo htmlspecialchars($item['title']); ?>">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" data-item-id="<?php echo $item['id']; ?>" data-item-title="<?php echo htmlspecialchars($item['title']); ?>">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    <?php elseif ($item['status'] == 'accepted'): ?>
                                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#shipModal" data-item-id="<?php echo $item['id']; ?>" data-item-title="<?php echo htmlspecialchars($item['title']); ?>">
                                                            <i class="fas fa-shipping-fast me-1"></i> Ship
                                                        </button>
                                                    <?php elseif ($item['status'] == 'shipped'): ?>
                                                        <?php if (!empty($item['tracking_number'])): ?>
                                                            <span class="text-muted small">Tracking: <?php echo $item['tracking_number']; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Shipped</span>
                                                        <?php endif; ?>
                                                    <?php elseif ($item['status'] == 'delivered'): ?>
                                                        <span class="text-success small">Completed</span>
                                                    <?php elseif ($item['status'] == 'rejected'): ?>
                                                        <span class="text-danger small">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">Seller Total:</td>
                                            <td class="fw-bold">$<?php echo number_format($sellerTotal, 2); ?></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Info and Order Summary -->
                <div class="col-lg-4">
                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="../uploads/profile_images/<?php echo $customer['profile_image']; ?>" alt="<?php echo $customer['username']; ?>" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                                <div>
                                    <h6 class="mb-0"><?php echo $customer['username']; ?></h6>
                                    <p class="text-muted mb-0 small">Customer since <?php echo date('M Y', strtotime($customer['created_at'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Contact Information</h6>
                                <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo $customer['email']; ?></p>
                                <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo !empty($customer['phone']) ? $customer['phone'] : 'Not provided'; ?></p>
                            </div>
                            
                            <div>
                                <h6>Shipping Address</h6>
                                <address class="mb-0">
                                    <?php echo $order['shipping_address']; ?><br>
                                    <?php echo $order['shipping_city']; ?>, <?php echo $order['shipping_state']; ?> <?php echo $order['shipping_zipcode']; ?><br>
                                    <?php echo $order['shipping_country']; ?>
                                </address>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($order['total_amount'] - $order['tax_amount'] - $order['shipping_amount'] + $order['discount_amount'], 2); ?></span>
                            </div>
                            <?php if ($order['discount_amount'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount</span>
                                    <span>-$<?php echo number_format($order['discount_amount'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax</span>
                                <span>$<?php echo number_format($order['tax_amount'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>$<?php echo number_format($order['shipping_amount'], 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-0">
                                <strong>Total</strong>
                                <strong class="text-primary">$<?php echo number_format($order['total_amount'], 2); ?></strong>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center small">
                                <span>Your portion</span>
                                <span class="fw-bold">$<?php echo number_format($sellerTotal, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="contact-customer.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-1"></i> Contact Customer
                                </a>
                                <a href="print-invoice.php?order_id=<?php echo $order_id; ?>" class="btn btn-outline-secondary" target="_blank">
                                    <i class="fas fa-print me-1"></i> Print Invoice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Accept Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1" aria-labelledby="acceptModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="acceptModalLabel">Accept Order Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $order_id); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to accept the order for "<span id="accept-item-title"></span>"?</p>
                    <p>This indicates that you can fulfill this item and will ship it soon.</p>
                    <input type="hidden" name="item_id" id="accept-item-id">
                    <input type="hidden" name="action" value="accept">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Accept</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Order Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $order_id); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to reject the order for "<span id="reject-item-title"></span>"?</p>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        <div class="form-text">Please provide a reason for rejection. This will be shared with the customer.</div>
                    </div>
                    <input type="hidden" name="item_id" id="reject-item-id">
                    <input type="hidden" name="action" value="reject">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ship Modal -->
<div class="modal fade" id="shipModal" tabindex="-1" aria-labelledby="shipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipModalLabel">Ship Order Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $order_id); ?>">
                <div class="modal-body">
                    <p>Mark "<span id="ship-item-title"></span>" as shipped?</p>
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">Tracking Number (Optional)</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number">
                        <div class="form-text">If available, provide a tracking number for the shipment.</div>
                    </div>
                    <input type="hidden" name="item_id" id="ship-item-id">
                    <input type="hidden" name="action" value="ship">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Shipped</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Accept Modal
        const acceptModal = document.getElementById('acceptModal');
        if (acceptModal) {
            acceptModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const itemId = button.getAttribute('data-item-id');
                const itemTitle = button.getAttribute('data-item-title');
                
                document.getElementById('accept-item-id').value = itemId;
                document.getElementById('accept-item-title').textContent = itemTitle;
            });
        }
        
        // Reject Modal
        const rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const itemId = button.getAttribute('data-item-id');
                const itemTitle = button.getAttribute('data-item-title');
                
                document.getElementById('reject-item-id').value = itemId;
                document.getElementById('reject-item-title').textContent = itemTitle;
            });
        }
        
        // Ship Modal
        const shipModal = document.getElementById('shipModal');
        if (shipModal) {
            shipModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const itemId = button.getAttribute('data-item-id');
                const itemTitle = button.getAttribute('data-item-title');
                
                document.getElementById('ship-item-id').value = itemId;
                document.getElementById('ship-item-title').textContent = itemTitle;
            });
        }
    });
</script>

<?php include_once 'includes/seller-footer.php'; ?>