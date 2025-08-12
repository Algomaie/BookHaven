<?php
// views/admin/sellers.php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/sellers.php');
    exit();
}

// Include config files

// Include required models


require_once '../../config/config.php';
require_once '../../config/database.php';

require_once '../../models/User.php';
require_once '../../models/Book.php';
// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$userModel = new User($db);
$bookModel = new Book($db);

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Prepare filter parameters
$filters = [
    'role' => 'seller',
    'search' => $search,
    'status' => $status,
    'sort' => $sort,
    'page' => $page,
    'limit' => $limit
];

// Get sellers with filters
$sellers = $userModel->getFilteredUsersByRole($filters);
$totalSellers = $userModel->getTotalFilteredUsersByRole($filters);

// Calculate total pages
$totalPages = ceil($totalSellers / $limit);

// Handle seller status toggle
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    $userModel->id = $user_id;
    
    if ($action === 'activate') {
        if ($userModel->setActiveStatus(true)) {
            $message = [
                'type' => 'success',
                'text' => 'Seller has been activated successfully.'
            ];
        } else {
            $message = [
                'type' => 'danger',
                'text' => 'Failed to activate seller.'
            ];
        }
    } elseif ($action === 'deactivate') {
        if ($userModel->setActiveStatus(false)) {
            $message = [
                'type' => 'success',
                'text' => 'Seller has been deactivated successfully.'
            ];
        } else {
            $message = [
                'type' => 'danger',
                'text' => 'Failed to deactivate seller.'
            ];
        }
    } elseif ($action === 'delete') {
        // Check if seller has books
        $bookModel->seller_id = $user_id;
        $sellerBooks = $bookModel->getBooksBySeller();
        
        if (!empty($sellerBooks)) {
            $message = [
                'type' => 'danger',
                'text' => 'This seller has books in the system. Please delete or reassign their books before deleting the seller.'
            ];
        } else {
            if ($userModel->delete()) {
                $message = [
                    'type' => 'success',
                    'text' => 'Seller has been deleted successfully.'
                ];
            } else {
                $message = [
                    'type' => 'danger',
                    'text' => 'Failed to delete seller.'
                ];
            }
        }
    }
    
    // Refresh seller list
    $sellers = $userModel->getFilteredUsersByRole($filters);
}

// Include admin header
include_once 'includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include_once 'includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Sellers</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                    <a href="add-user.php?role=seller" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Seller
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Sellers</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Name, Email, Username..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>All</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="books_count" <?php echo $sort === 'books_count' ? 'selected' : ''; ?>>Books Count</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="limit" class="form-label">Show</label>
                            <select class="form-select" id="limit" name="limit">
                                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </div>
                    </form>
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
                                        Total Sellers</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalSellers; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-store fa-2x text-gray-300"></i>
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
                                        Active Sellers</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userModel->getTotalActiveUsersByRole('seller'); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                        Inactive Sellers</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $userModel->getTotalInactiveUsersByRole('seller'); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
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
                                        Books Listed</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $bookModel->getTotalBooks(); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sellers Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Seller Accounts</h6>
                    <span>
                        Showing <?php echo ($totalSellers > 0) ? (($page - 1) * $limit) + 1 : 0; ?> to 
                        <?php echo min($page * $limit, $totalSellers); ?> of 
                        <?php echo $totalSellers; ?> sellers
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($sellers)): ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-data.svg" alt="No Sellers" style="max-width: 200px;" class="mb-3">
                            <h4>No sellers found</h4>
                            <p class="text-muted">Try changing your search criteria or add a new seller.</p>
                            <a href="add-user.php?role=seller" class="btn btn-primary mt-2">
                                <i class="fas fa-plus-circle me-1"></i> Add New Seller
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="sellersTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Seller</th>
                                        <th>Email</th>
                                        <th>Books</th>
                                        <th>Sales</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sellers as $seller): 
                                        // Get seller's books count
                                        $bookModel->seller_id = $seller['id'];
                                        $booksCount = count($bookModel->getBooksBySeller());
                                        
                                        // Get seller's sales - this would be a real method in your Order model
                                        $sellerSales = 0;
                                    ?>
                                        <tr>
                                            <td><?php echo $seller['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo !empty($seller['profile_image']) ? '../uploads/profile_images/' . $seller['profile_image'] : '../assets/images/default-profile.jpg'; ?>" alt="<?php echo $seller['username']; ?>" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold"><?php echo $seller['username']; ?></div>
                                                        <div class="small text-muted">
                                                            <?php echo !empty($seller['first_name']) ? $seller['first_name'] . ' ' . $seller['last_name'] : 'No name provided'; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $seller['email']; ?></td>
                                            <td><?php echo $booksCount; ?></td>
                                            <td>$<?php echo number_format($sellerSales, 2); ?></td>
                                            <td>
                                                <?php if ($seller['is_active']): ?>
                                                    <span class="badge bg-success p-2">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger p-2">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($seller['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="seller-details.php?id=<?php echo $seller['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($seller['is_active']): ?>
                                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#deactivateModal" data-user-id="<?php echo $seller['id']; ?>" data-username="<?php echo $seller['username']; ?>" title="Deactivate">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#activateModal" data-user-id="<?php echo $seller['id']; ?>" data-username="<?php echo $seller['username']; ?>" title="Activate">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-user-id="<?php echo $seller['id']; ?>" data-username="<?php echo $seller['username']; ?>" data-books-count="<?php echo $booksCount; ?>" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Sellers pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=1&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&sort=<?php echo $sort; ?>">First</a>
                                </li>
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&sort=<?php echo $sort; ?>">Previous</a>
                                </li>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&sort=<?php echo $sort; ?>">Next</a>
                                </li>
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $totalPages; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>&sort=<?php echo $sort; ?>">Last</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Activate Seller Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activateModalLabel">Activate Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to activate the seller account for "<span id="activate-username"></span>"?</p>
                    <p>This will allow the seller to log in and manage their books and orders.</p>
                    <input type="hidden" name="user_id" id="activate-user-id">
                    <input type="hidden" name="action" value="activate">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Activate Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deactivate Seller Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deactivateModalLabel">Deactivate Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to deactivate the seller account for "<span id="deactivate-username"></span>"?</p>
                    <p class="text-danger">This will prevent the seller from logging in and managing their books and orders.</p>
                    <input type="hidden" name="user_id" id="deactivate-user-id">
                    <input type="hidden" name="action" value="deactivate">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Deactivate Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Seller Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to delete the seller account for "<span id="delete-username"></span>"?</p>
                    <div id="delete-warning" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This seller has <span id="delete-books-count"></span> books in the system. 
                        You must delete or reassign these books before deleting the seller account.
                    </div>
                    <p class="text-danger fw-bold mb-0">This action cannot be undone!</p>
                    <input type="hidden" name="user_id" id="delete-user-id">
                    <input type="hidden" name="action" value="delete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="delete-confirm-btn">Delete Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export functionality -->
<script>
    // Export table to CSV
    function exportToCSV() {
        const table = document.getElementById('sellersTable');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Skip the actions column
                if (j !== 7) {
                    // Get text content, removing extra spaces and HTML
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').trim();
                    
                    // Remove multiple spaces
                    data = data.replace(/\s{2,}/g, ' ');
                    
                    // Escape double quotes and surround with quotes
                    data = '"' + data.replace(/"/g, '""') + '"';
                    
                    row.push(data);
                }
            }
            
            csv.push(row.join(','));
        }
        
        // Download CSV file
        const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'sellers_list_' + new Date().toISOString().slice(0, 10) + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Activate modal
        const activateModal = document.getElementById('activateModal');
        if (activateModal) {
            activateModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');
                
                document.getElementById('activate-user-id').value = userId;
                document.getElementById('activate-username').textContent = username;
            });
        }
        
        // Deactivate modal
        const deactivateModal = document.getElementById('deactivateModal');
        if (deactivateModal) {
            deactivateModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');
                
                document.getElementById('deactivate-user-id').value = userId;
                document.getElementById('deactivate-username').textContent = username;
            });
        }
        
        // Delete modal
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');
                const booksCount = parseInt(button.getAttribute('data-books-count'));
                
                document.getElementById('delete-user-id').value = userId;
                document.getElementById('delete-username').textContent = username;
                
                // Show warning if seller has books
                const warningElement = document.getElementById('delete-warning');
                const deleteButton = document.getElementById('delete-confirm-btn');
                
                if (booksCount > 0) {
                    warningElement.classList.remove('d-none');
                    document.getElementById('delete-books-count').textContent = booksCount;
                    deleteButton.disabled = true;
                } else {
                    warningElement.classList.add('d-none');
                    deleteButton.disabled = false;
                }
            });
        }
    });
</script>

<?php include_once 'includes/admin-footer.php'; ?>