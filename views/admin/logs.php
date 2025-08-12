<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

$database = new Database();
$db = $database->connect();
$logModel = new ActivityLog($db);

// Clear logs
if(isset($_POST['clear_logs']) && isset($_POST['confirm_clear'])) {
    $logModel->clearLogs();
    header('Location: /views/admin/logs.php?cleared=1');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 20;
$offset = ($page - 1) * $recordsPerPage;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$actionFilter = isset($_GET['action']) ? $_GET['action'] : '';
$userFilter = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Get logs with pagination and filters
$logs = $logModel->getLogs($offset, $recordsPerPage, $search, $actionFilter, $userFilter, $dateFrom, $dateTo);
$totalRecords = $logModel->getTotalLogs($search, $actionFilter, $userFilter, $dateFrom, $dateTo);
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get log actions for filter dropdown
$logActions = $logModel->getLogActions();

// Get users for filter dropdown
require_once __DIR__ . '/../../models/User.php';
$userModel = new User($db);
$users = $userModel->getAllUsers();

require_once __DIR__ . '/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Activity Logs</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/views/admin/export-logs.php" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-download me-1"></i> Export
                    </a>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                        <i class="fas fa-trash me-1"></i> Clear Logs
                    </button>
                </div>
            </div>

            <?php if(isset($_GET['cleared']) && $_GET['cleared'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Activity logs have been cleared successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search logs" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="action" class="form-select">
                                <option value="">All Actions</option>
                                <?php foreach($logActions as $action): ?>
                                    <option value="<?php echo $action['action']; ?>" <?php echo $actionFilter === $action['action'] ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($action['action']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $userFilter == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo $user['username']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_from" placeholder="From Date" value="<?php echo $dateFrom; ?>">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control" name="date_to" placeholder="To Date" value="<?php echo $dateTo; ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i> Activity Logs
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($logs && count($logs) > 0): ?>
                                    <?php foreach($logs as $log): ?>
                                        <tr>
                                            <td><?php echo $log['id']; ?></td>
                                            <td>
                                                <?php if($log['user_id']): ?>
                                                    <a href="/views/admin/edit-user.php?id=<?php echo $log['user_id']; ?>">
                                                        <?php echo $log['username']; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">System</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $badgeClass = 'bg-secondary';
                                                    if(in_array($log['action'], ['login', 'register', 'activate'])) {
                                                        $badgeClass = 'bg-success';
                                                    } elseif(in_array($log['action'], ['logout', 'deactivate'])) {
                                                        $badgeClass = 'bg-warning';
                                                    } elseif(in_array($log['action'], ['delete', 'failed_login'])) {
                                                        $badgeClass = 'bg-danger';
                                                    } elseif(in_array($log['action'], ['create', 'add'])) {
                                                        $badgeClass = 'bg-primary';
                                                    } elseif(in_array($log['action'], ['update', 'edit'])) {
                                                        $badgeClass = 'bg-info';
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($log['action']); ?></span>
                                            </td>
                                            <td><?php echo $log['description']; ?></td>
                                            <td><?php echo $log['ip_address']; ?></td>
                                            <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No activity logs found</td>
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
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&action=<?php echo $actionFilter; ?>&user_id=<?php echo $userFilter; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&action=<?php echo $actionFilter; ?>&user_id=<?php echo $userFilter; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&action=<?php echo $actionFilter; ?>&user_id=<?php echo $userFilter; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">
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

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearLogsModalLabel">Clear Activity Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Warning! This action cannot be undone.
                </div>
                <p>Are you sure you want to clear all activity logs? This action will permanently delete all log entries from the database.</p>
                <form id="clearLogsForm" action="" method="POST">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirm_clear" name="confirm_clear" required>
                        <label class="form-check-label" for="confirm_clear">
                            I understand that this action cannot be undone.
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="clearLogsForm" name="clear_logs" class="btn btn-danger">Clear All Logs</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/admin-footer.php';
?>