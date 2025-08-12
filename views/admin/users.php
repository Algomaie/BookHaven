<?php
// views/admin/users.php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/users.php');
    exit();
}

// Include config files
require_once '../../config/config.php';
require_once '../../config/database.php';

// Include required models
require_once '../../models/User.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$userModel = new User($db);

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Prepare filter parameters
$filters = [
    'role' => 'admin',
    'search' => $search,
    'status' => $status,
    'page' => $page,
    'limit' => $limit
];

// Get admin users with filters
$users = $userModel->getFilteredUsersByRole($filters);
$totalUsers = $userModel->getTotalFilteredUsersByRole($filters);

// Calculate total pages
$totalPages = ceil($totalUsers / $limit);

// Handle user status toggle
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    // Don't allow actions on your own account
    if ($user_id == $_SESSION['user_id']) {
        $message = [
            'type' => 'danger',
            'text' => 'You cannot modify your own account status.'
        ];
    } else {
        $userModel->id = $user_id;
        
        if ($action === 'activate') {
            if ($userModel->setActiveStatus(true)) {
                $message = [
                    'type' => 'success',
                    'text' => 'User has been activated successfully.'
                ];
            } else {
                $message = [
                    'type' => 'danger',
                    'text' => 'Failed to activate user.'
                ];
            }
        } elseif ($action === 'deactivate') {
            if ($userModel->setActiveStatus(false)) {
                $message = [
                    'type' => 'success',
                    'text' => 'User has been deactivated successfully.'
                ];
            } else {
                $message = [
                    'type' => 'danger',
                    'text' => 'Failed to deactivate user.'
                ];
            }
        } elseif ($action === 'delete') {
            if ($userModel->delete()) {
                $message = [
                    'type' => 'success',
                    'text' => 'User has been deleted successfully.'
                ];
            } else {
                $message = [
                    'type' => 'danger',
                    'text' => 'Failed to delete user.'
                ];
            }
        }
        
        // Refresh user list
        $users = $userModel->getFilteredUsersByRole($filters);
    }
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
                <h1 class="h2">Manage Admin Users</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                    <a href="add-user.php?role=admin" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Admin
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
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Admin Users</h5>
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
                            <label for="limit" class="form-label">Show</label>
                            <select class="form-select" id="limit" name="limit">
                                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Apply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Admin Users Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Admin Accounts</h6>
                    <span>
                        Showing <?php echo ($totalUsers > 0) ? (($page - 1) * $limit) + 1 : 0; ?> to 
                        <?php echo min($page * $limit, $totalUsers); ?> of 
                        <?php echo $totalUsers; ?> admins
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-data.svg" alt="No Admins" style="max-width: 200px;" class="mb-3">
                            <h4>No admin users found</h4>
                            <p class="text-muted">Try changing your search criteria or add a new admin user.</p>
                            <a href="add-user.php?role=admin" class="btn btn-primary mt-2">
                                <i class="fas fa-plus-circle me-1"></i> Add New Admin
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="adminsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): 
                                        $isCurrentUser = ($user['id'] == $_SESSION['user_id']);
                                    ?>
                                        <tr <?php echo $isCurrentUser ? 'class="table-primary"' : ''; ?>>
                                            <td><?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo !empty($user['profile_image']) ? '../uploads/profile_images/' . $user['profile_image'] : '../assets/images/default-profile.jpg'; ?>" alt="<?php echo $user['username']; ?>" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold">
                                                            <?php echo $user['username']; ?>
                                                            <?php if ($isCurrentUser): ?>
                                                                <span class="badge bg-info">You</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="small text-muted">
                                                            <?php echo !empty($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : 'No name provided'; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td>
                                                <?php echo !empty($user['last_login']) ? date('M d, Y, g:i a', strtotime($user['last_login'])) : 'Never'; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge bg-success p-2">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger p-2">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if (!$isCurrentUser): ?>
                                                        <?php if ($user['is_active']): ?>
                                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#deactivateModal" data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo $user['username']; ?>" title="Deactivate">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#activateModal" data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo $user['username']; ?>" title="Activate">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-user-id="<?php echo $user['id']; ?>" data-username="<?php echo $user['username']; ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Cannot modify your own account">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
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
                            <nav aria-label="Admin users pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=1&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">First</a>
                                    </li>
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">Previous</a>
                                    </li>
                                    
                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">Next</a>
                                    </li>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $totalPages; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">Last</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Permissions Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Admin Permissions Information</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <h5><i class="fas fa-info-circle me-2"></i>About Admin Access</h5>
                        <p>
                            Admin users have full access to all system functions, including managing books, users, orders, and system settings.
                            Be careful when adding new admin users, as they will have complete control over the system.
                        </p>
                        <h6 class="mt-3">Admin Capabilities:</h6>
                        <ul class="mb-0">
                            <li>User management (create, edit, activate/deactivate all users)</li>
                            <li>Book management (approve, edit, delete any book)</li>
                            <li>Orders management (view, process, update all orders)</li>
                            <li>System settings (modify site settings, email templates, etc.)</li>
                            <li>Financial data access (sales reports, revenue information)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Activate User Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activateModalLabel">Activate Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to activate the admin account for "<span id="activate-username"></span>"?</p>
                    <p>This will allow the user to log in and access all admin functionalities.</p>
                    <input type="hidden" name="user_id" id="activate-user-id">
                    <input type="hidden" name="action" value="activate">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Activate User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deactivate User Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deactivateModalLabel">Deactivate Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-body">
                    <p>Are you sure you want to deactivate the admin account for "<span id="deactivate-username"></span>"?</p>
                    <p class="text-danger">This will prevent the user from logging in and accessing admin functionalities.</p>
                    <input type="hidden" name="user_id" id="deactivate-user-id">
                    <input type="hidden" name="action" value="deactivate">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Deactivate User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Deleting an admin user is a permanent action and cannot be undone.
                    </div>
                    <p>Are you sure you want to delete the admin account for "<span id="delete-username"></span>"?</p>
                    <p>All user data, including activity logs and changes made by this admin, will be permanently removed from the system.</p>
                    <input type="hidden" name="user_id" id="delete-user-id">
                    <input type="hidden" name="action" value="delete">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User Permanently</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export functionality -->
<script>
    // Export table to CSV
    function exportToCSV() {
        const table = document.getElementById('adminsTable');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Skip the actions column
                if (j !== 6) {
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
        link.setAttribute('download', 'admin_users_' + new Date().toISOString().slice(0, 10) + '.csv');
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
                
                document.getElementById('delete-user-id').value = userId;
                document.getElementById('delete-username').textContent = username;
            });
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>

<?php include_once 'includes/admin-footer.php'; ?>