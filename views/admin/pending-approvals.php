<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Book.php';
require_once __DIR__ . '/../../models/Category.php';

$database = new Database();
$db = $database->connect();
$bookModel = new Book($db);
$categoryModel = new Category($db);

// Handle book approval or rejection
if(isset($_GET['action']) && isset($_GET['id'])) {
    $bookId = $_GET['id'];
    $action = $_GET['action'];
    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
    
    if($action === 'approve') {
        $bookModel->updateBookStatus($bookId, 'approved');
    } elseif($action === 'reject') {
        $bookModel->updateBookStatus($bookId, 'rejected', $reason);
    }
    header('Location: /views/admin/pending-approvals.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

// Get pending books with pagination and filters
$pendingBooks = $bookModel->getPendingBooks($offset, $recordsPerPage, $search, $categoryFilter);
$totalRecords = $bookModel->getTotalPendingBooks($search, $categoryFilter);
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get all categories for filter dropdown
$categories = $categoryModel->getAllCategories();

require_once __DIR__ . '/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pending Book Approvals</h1>
            </div>

            <!-- Search and Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by title, author or seller" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <a href="/views/admin/pending-approvals.php" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pending Books Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-clock me-1"></i> Pending Book Approvals</div>
                    <span class="badge bg-warning rounded-pill"><?php echo $totalRecords; ?> Pending</span>
                </div>
                <div class="card-body">
                    <?php if($pendingBooks && count($pendingBooks) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Author</th>
                                        <th>Seller</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($pendingBooks as $book): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if($book['cover_image']): ?>
                                                        <img src="/uploads/book_covers/<?php echo $book['cover_image']; ?>" width="40" height="50" class="me-2" alt="<?php echo $book['title']; ?>">
                                                    <?php else: ?>
                                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 50px;">
                                                            <i class="fas fa-book"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php echo $book['title']; ?>
                                                </div>
                                            </td>
                                            <td><?php echo $book['author']; ?></td>
                                            <td><?php echo $book['seller_name']; ?></td>
                                            <td>$<?php echo number_format($book['price'], 2); ?></td>
                                            <td><?php echo $book['category_name']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/views/admin/book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-success approve-book" data-book-id="<?php echo $book['id']; ?>" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger reject-book" data-book-id="<?php echo $book['id']; ?>" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>No pending book approvals at the moment.
                        </div>
                    <?php endif; ?>

                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&category=<?php echo $categoryFilter; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&category=<?php echo $categoryFilter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&category=<?php echo $categoryFilter; ?>">
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

<script>
    document.querySelectorAll('.approve-book').forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book-id');
            if(confirm('Are you sure you want to approve this book?')) {
                window.location.href = `/views/admin/pending-approvals.php?id=${bookId}&action=approve`;
            }
        });
    });
    
    document.querySelectorAll('.reject-book').forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book-id');
            if(confirm('Are you sure you want to reject this book?')) {
                const reason = prompt('Please provide a reason for rejection:');
                if(reason) {
                    window.location.href = `/views/admin/pending-approvals.php?id=${bookId}&action=reject&reason=${encodeURIComponent(reason)}`;
                }
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/admin-footer.php';
?>