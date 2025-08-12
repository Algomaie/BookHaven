<?php
// views/seller/books.php
// Start session
session_start();

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../../views/auth/login.php');
    exit;
}

// // Include config files
// require_once '../config/config.php';
// require_once '../config/database.php';

// // Include required models
// require_once '../models/Book.php';
// require_once '../models/Category.php';

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

// Get seller ID from session
$seller_id = $_SESSION['user_id'];

// Get all categories for filter
$categories = $categoryModel->getCategories();

// Set default filters
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$resultsPerPage = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$stock = isset($_GET['stock']) ? $_GET['stock'] : '';

// Process book deletion if requested
$deleteMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    
    if ($book_id > 0) {
        $bookModel->id = $book_id;
        $bookModel->seller_id = $seller_id; // Ensure only the seller's own books can be deleted
        
        if ($bookModel->delete()) {
            $deleteMessage = [
                'type' => 'success',
                'text' => 'Book has been successfully deleted.'
            ];
        } else {
            $deleteMessage = [
                'type' => 'danger',
                'text' => 'Failed to delete book. Please try again.'
            ];
        }
    }
}

// Get seller's books with optional filters
$bookModel->seller_id = $seller_id;

// Build filter parameters
$filters = [
    'seller_id' => $seller_id,
    'category_id' => $category,
    'status' => $status,
    'stock' => $stock,
    'search' => $search,
    'page' => $currentPage,
    'limit' => $resultsPerPage
];

// Get books with filters
$books = $bookModel->getFilteredBooks($filters);
$totalBooks = $bookModel->getTotalFilteredBooks($filters);

// Calculate total pages for pagination
$totalPages = ceil($totalBooks / $resultsPerPage);

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
            
            <?php if ($deleteMessage): ?>
                <div class="alert alert-<?php echo $deleteMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $deleteMessage['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Books</h5>
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
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="stock" class="form-label">Stock</label>
                            <select class="form-select" id="stock" name="stock">
                                <option value="" <?php echo $stock === '' ? 'selected' : ''; ?>>All</option>
                                <option value="in_stock" <?php echo $stock === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                                <option value="low_stock" <?php echo $stock === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                                <option value="out_of_stock" <?php echo $stock === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="limit" class="form-label">Limit</label>
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
            
            <!-- Books Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card h-100 py-2 border-left-primary">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Books</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalBooks; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card h-100 py-2 border-left-success">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Approved Books</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $approvedCount = 0;
                                        foreach ($books as $book) {
                                            if ($book['approval_status'] === 'approved') {
                                                $approvedCount++;
                                            }
                                        }
                                        echo $approvedCount; 
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
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card h-100 py-2 border-left-warning">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pending Books</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $pendingCount = 0;
                                        foreach ($books as $book) {
                                            if ($book['approval_status'] === 'pending') {
                                                $pendingCount++;
                                            }
                                        }
                                        echo $pendingCount; 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card h-100 py-2 border-left-danger">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Out of Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $outOfStockCount = 0;
                                        foreach ($books as $book) {
                                            if ($book['stock_quantity'] <= 0) {
                                                $outOfStockCount++;
                                            }
                                        }
                                        echo $outOfStockCount; 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Books Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Your Books</h6>
                    <span>
                        Showing <?php echo min($totalBooks, (($currentPage - 1) * $resultsPerPage) + 1); ?> to 
                        <?php echo min($totalBooks, $currentPage * $resultsPerPage); ?> of 
                        <?php echo $totalBooks; ?> books
                    </span>
                </div>
                <div class="card-body">
                    <?php if (empty($books)): ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-books.svg" alt="No Books" style="max-width: 150px;" class="mb-3">
                            <h5>No books found</h5>
                            <p class="text-muted mb-0">You haven't added any books yet or none match your filter criteria.</p>
                            <a href="add-book.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-1"></i> Add Your First Book
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="booksTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th style="width: 80px;">Cover</th>
                                        <th>Book Details</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = ($currentPage - 1) * $resultsPerPage + 1; ?>
                                    <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td>
                                                <img src="/../../uploads/books/<?php echo $book['cover_image']; ?>" class="img-thumbnail" alt="<?php echo $book['title']; ?>" style="width: 50px;">
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="text-primary font-weight-bold" target="_blank">
                                                        <?php echo $book['title']; ?>
                                                    </a>
                                                    <span class="text-muted">By: <?php echo $book['author']; ?></span>
                                                    <?php if (!empty($book['isbn'])): ?>
                                                        <small class="text-muted">ISBN: <?php echo $book['isbn']; ?></small>
                                                    <?php endif; ?>
                                                    <small class="text-muted">Added: <?php echo date('M d, Y', strtotime($book['created_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo $book['category_name']; ?></td>
                                            <td>
                                                <?php if ($book['discount_percent'] > 0): ?>
                                                    <div class="text-primary font-weight-bold">
                                                        $<?php echo number_format($book['price'] * (1 - $book['discount_percent'] / 100), 2); ?>
                                                    </div>
                                                    <span class="text-muted text-decoration-line-through">$<?php echo number_format($book['price'], 2); ?></span>
                                                    <span class="badge bg-danger"><?php echo number_format($book['discount_percent']); ?>% OFF</span>
                                                <?php else: ?>
                                                    <span class="text-primary font-weight-bold">$<?php echo number_format($book['price'], 2); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($book['stock_quantity'] <= 0): ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php elseif ($book['stock_quantity'] < 5): ?>
                                                    <span class="badge bg-warning text-dark">Low: <?php echo $book['stock_quantity']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $book['stock_quantity']; ?> in stock</span>
                                                <?php endif; ?>
                                                <a href="update-stock.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-secondary mt-1 d-block">
                                                    <i class="fas fa-sync-alt"></i> Update
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($book['approval_status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-3 py-2">Pending Review</span>
                                                <?php elseif ($book['approval_status'] === 'approved'): ?>
                                                    <span class="badge bg-success px-3 py-2">Approved</span>
                                                <?php elseif ($book['approval_status'] === 'rejected'): ?>
                                                    <span class="badge bg-danger px-3 py-2">Rejected</span>
                                                    <?php if (!empty($book['rejection_reason'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1 d-block" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo htmlspecialchars($book['rejection_reason']); ?>">
                                                            <i class="fas fa-info-circle"></i> Reason
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-info" target="_blank" data-bs-toggle="tooltip" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteBookModal" 
                                                            data-book-id="<?php echo $book['id']; ?>" 
                                                            data-book-title="<?php echo htmlspecialchars($book['title']); ?>"
                                                            data-bs-toggle="tooltip" title="Delete">
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
                        <nav aria-label="Books pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=1&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&stock=<?php echo $stock; ?>&search=<?php echo urlencode($search); ?>">First</a>
                                </li>
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $currentPage - 1; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&stock=<?php echo $stock; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                </li>
                                
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $i; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&stock=<?php echo $stock; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $currentPage + 1; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&stock=<?php echo $stock; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                </li>
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=<?php echo $totalPages; ?>&limit=<?php echo $resultsPerPage; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&stock=<?php echo $stock; ?>&search=<?php echo urlencode($search); ?>">Last</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Delete Book Modal -->
<div class="modal fade" id="deleteBookModal" tabindex="-1" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBookModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the book "<span id="book-title-placeholder"></span>"?</p>
                <p class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i> This action cannot be undone and will permanently remove the book from your inventory.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="book_id" id="delete-book-id">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger">Delete Book</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Export functionality -->
<script>
    // Export table to CSV
    function exportToCSV() {
        const table = document.getElementById('booksTable');
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                // Skip the cover image and actions columns
                if (j !== 1 && j !== 7) {
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
        link.setAttribute('download', 'books_inventory_' + new Date().toISOString().slice(0, 10) + '.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Delete modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('deleteBookModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const bookId = button.getAttribute('data-book-id');
                const bookTitle = button.getAttribute('data-book-title');
                
                document.getElementById('book-title-placeholder').textContent = bookTitle;
                document.getElementById('delete-book-id').value = bookId;
            });
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<?php
// Include seller footer
include_once 'includes/seller-footer.php';
?>