<?php
// admin/books.php
// Start session
session_start();

// Check if admin is logged in, redirect if not
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

// Include required files
// Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/Book.php';
require_once '../../models/Category.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$bookModel = new Book($db);
$categoryModel = new Category($db);

// Get all categories
$categories = $categoryModel->getCategories();

// Set default filters
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$resultsPerPage = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build filter parameters
$filters = [
    'category_id' => $category,
    'status' => $status,
    'search' => $search,
    'page' => $currentPage,
    'limit' => $resultsPerPage
];

// Get books with filters
$books = $bookModel->getFilteredBooks($filters);
$totalBooks = $bookModel->getTotalFilteredBooks($filters);
$totalPages = ceil($totalBooks / $resultsPerPage);

// Process actions
$actionMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    
    if ($bookId > 0) {
        $bookModel->id = $bookId;
        
        if ($_POST['action'] === 'approve') {
            if ($bookModel->updateStatus('approved')) {
                $actionMessage = [
                    'type' => 'success',
                    'text' => 'Book has been approved and is now publicly available.'
                ];
            } else {
                $actionMessage = [
                    'type' => 'danger',
                    'text' => 'Failed to approve book. Please try again.'
                ];
            }
        } elseif ($_POST['action'] === 'reject') {
            $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
            if ($bookModel->updateStatus('rejected', $reason)) {
                $actionMessage = [
                    'type' => 'success',
                    'text' => 'Book has been rejected with the provided reason.'
                ];
            } else {
                $actionMessage = [
                    'type' => 'danger',
                    'text' => 'Failed to reject book. Please try again.'
                ];
            }
        } elseif ($_POST['action'] === 'feature') {
            if ($bookModel->setFeatured(true)) {
                $actionMessage = [
                    'type' => 'success',
                    'text' => 'Book has been marked as featured.'
                ];
            } else {
                $actionMessage = [
                    'type' => 'danger',
                    'text' => 'Failed to feature book. Please try again.'
                ];
            }
        } elseif ($_POST['action'] === 'unfeature') {
            if ($bookModel->setFeatured(false)) {
                $actionMessage = [
                    'type' => 'success',
                    'text' => 'Book has been removed from featured.'
                ];
            } else {
                $actionMessage = [
                    'type' => 'danger',
                    'text' => 'Failed to unfeature book. Please try again.'
                ];
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($bookModel->delete()) {
                $actionMessage = [
                    'type' => 'success',
                    'text' => 'Book has been deleted successfully.'
                ];
            } else {
                $actionMessage = [
                    'type' => 'danger',
                    'text' => 'Failed to delete book. Please try again.'
                ];
            }
        }
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
                <h1 class="h2">Manage Books</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                    <a href="add-book.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Book
                    </a>
                </div>
            </div>
            
            <?php if ($actionMessage): ?>
                <div class="alert alert-<?php echo $actionMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $actionMessage['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filter Books</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Title, Author, ISBN..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="0">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="limit" class="form-label">Show</label>
                            <select class="form-select" id="limit" name="limit">
                                <option value="10" <?php echo $resultsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo $resultsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $resultsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $resultsPerPage == 100 ? 'selected' : ''; ?>>100</option>
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
            
            <!-- Books Table -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Books List</h5>
                    <span>
                        Showing <?php echo min($totalBooks, (($currentPage - 1) * $resultsPerPage) + 1); ?> to 
                        <?php echo min($totalBooks, $currentPage * $resultsPerPage); ?> of 
                        <?php echo $totalBooks; ?> books
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 80px;">Cover</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Date Added</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($books)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-3">No books found matching your criteria.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $counter = ($currentPage - 1) * $resultsPerPage + 1; ?>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td>
                                                <img src="../uploads/book_covers/<?php echo $book['cover_image']; ?>" class="img-thumbnail" alt="<?php echo $book['title']; ?>" width="50">
                                            </td>
                                            <td>
                                                <a href="book-details.php?id=<?php echo $book['id']; ?>" class="fw-bold text-decoration-none">
                                                    <?php echo $book['title']; ?>
                                                </a>
                                                <?php if ($book['is_featured']): ?>
                                                    <span class="badge bg-primary ms-1">Featured</span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted">ISBN: <?php echo $book['isbn'] ?: 'N/A'; ?></small>
                                            </td>
                                            <td><?php echo $book['author']; ?></td>
                                            <td><?php echo $book['category_name']; ?></td>
                                            <td>
                                                <?php if ($book['discount_percent'] > 0): ?>
                                                    <span class="text-primary">$<?php echo number_format($book['price'] * (1 - $book['discount_percent'] / 100), 2); ?></span>
                                                    <br>
                                                    <small class="text-muted text-decoration-line-through">$<?php echo number_format($book['price'], 2); ?></small>
                                                <?php else: ?>
                                                    <span class="text-primary">$<?php echo number_format($book['price'], 2); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($book['approval_status'] === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($book['approval_status'] === 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php elseif ($book['approval_status'] === 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal" data-book-id="<?php echo $book['id']; ?>" data-book-title="<?php echo $book['title']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <?php if ($book['approval_status'] === 'pending'): ?>
                                                            <li>
                                                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                                    <input type="hidden" name="action" value="approve">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-check text-success me-2"></i> Approve
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#rejectModal" data-book-id="<?php echo $book['id']; ?>" data-book-title="<?php echo $book['title']; ?>">
                                                                    <i class="fas fa-times text-danger me-2"></i> Reject
                                                                </button>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if ($book['is_featured']): ?>
                                                            <li>
                                                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                                    <input type="hidden" name="action" value="unfeature">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-star text-warning me-2"></i> Remove from Featured
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        <?php else: ?>
                                                            <li>
                                                                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                                    <input type="hidden" name="action" value="feature">
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="far fa-star text-warning me-2"></i> Add to Featured
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a href="../book-details.php?id=<?php echo $book['id']; ?>" class="dropdown-item" target="_blank">
                                                                <i class="fas fa-external-link-alt me-2"></i> View on Site
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <nav>
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=1&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">First</a>
                            </li>
                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $currentPage - 1; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                            </li>
                            
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $i; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $currentPage + 1; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                            </li>
                            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $totalPages; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>">Last</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the book "<span id="delete-book-title"></span>"?</p>
                <p class="text-danger mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="book_id" id="delete-book-id">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="modal-body">
                    <p>You are rejecting the book "<span id="reject-book-title"></span>".</p>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        <div class="form-text">Please provide a reason for rejection. This will be sent to the seller.</div>
                    </div>
                    <input type="hidden" name="book_id" id="reject-book-id">
                    <input type="hidden" name="action" value="reject">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom JavaScript for this page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete Modal
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const bookId = button.getAttribute('data-book-id');
                const bookTitle = button.getAttribute('data-book-title');
                
                document.getElementById('delete-book-id').value = bookId;
                document.getElementById('delete-book-title').textContent = bookTitle;
            });
        }
        
        // Reject Modal
        const rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const bookId = button.getAttribute('data-book-id');
                const bookTitle = button.getAttribute('data-book-title');
                
                document.getElementById('reject-book-id').value = bookId;
                document.getElementById('reject-book-title').textContent = bookTitle;
            });
        }
    });
    
    // Export to CSV
    function exportToCSV() {
        const table = document.querySelector('table');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Skip the actions column and image column
                if (j !== 1 && j !== 8) {
                    // Clean the text content (remove extra spaces, newlines, etc.)
                    let data = cols[j].textContent.replace(/(\r\n|\n|\r)/gm, ' ').trim();
                    // Escape double quotes
                    data = data.replace(/"/g, '""');
                    // Add double quotes around the field
                    row.push('"' + data + '"');
                }
            }
            
            csv.push(row.join(','));
        }
        
        // Create CSV file
        const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const encodedUri = encodeURI(csvContent);
        
        // Create a link and trigger download
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', 'books_list.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php
// Include admin footer
include_once 'includes/admin-footer.php';
?>