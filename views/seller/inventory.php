<?php
// views/seller/inventory.php
session_start();

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../auth/login.php');
    exit;
}

// Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/Book.php';
require_once '../../models/Category.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$bookModel = new Book($db);
$categoryModel = new Category($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];
$bookModel->seller_id = $seller_id;

// Get all categories for filter dropdown
$categories = $categoryModel->getCategories();

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Set up filters
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get books based on filters
$filters = [
    'category_id' => $category_id,
    'search' => $search,
    'page' => $page,
    'limit' => $limit
];

// Apply specific filter
if ($filter === 'low_stock') {
    // Get low stock books (less than 5)
    $query = "SELECT b.*, c.name as category_name
              FROM books b
              LEFT JOIN categories c ON b.category_id = c.id
              WHERE b.seller_id = ? AND b.stock_quantity < 5
              ORDER BY b.stock_quantity ASC
              LIMIT ?, ?";
    
    $stmt = $db->prepare($query);
    $offset = ($page - 1) * $limit;
    $stmt->bind_param("iii", $seller_id, $offset, $limit);
    $stmt->execute();
    $books_result = $stmt->get_result();
    
    $books = [];
    while ($row = $books_result->fetch_assoc()) {
        $books[] = $row;
    }
    
    // Count total low stock books
    $count_query = "SELECT COUNT(*) as total FROM books WHERE seller_id = ? AND stock_quantity < 5";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bind_param("i", $seller_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_books = $count_row['total'];
} elseif ($filter === 'out_of_stock') {
    // Get out of stock books
    $query = "SELECT b.*, c.name as category_name
              FROM books b
              LEFT JOIN categories c ON b.category_id = c.id
              WHERE b.seller_id = ? AND b.stock_quantity = 0
              ORDER BY b.updated_at DESC
              LIMIT ?, ?";
    
    $stmt = $db->prepare($query);
    $offset = ($page - 1) * $limit;
    $stmt->bind_param("iii", $seller_id, $offset, $limit);
    $stmt->execute();
    $books_result = $stmt->get_result();
    
    $books = [];
    while ($row = $books_result->fetch_assoc()) {
        $books[] = $row;
    }
    
    // Count total out of stock books
    $count_query = "SELECT COUNT(*) as total FROM books WHERE seller_id = ? AND stock_quantity = 0";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bind_param("i", $seller_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_books = $count_row['total'];
} else {
    // Get all books with filters
    $books = $bookModel->getFilteredBooks($filters);
    $total_books = $bookModel->getTotalFilteredBooks($filters);
}

// Calculate pagination
$total_pages = ceil($total_books / $limit);

// Handle stock update
$update_message = '';
if(isset($_POST['update_stock'])) {
    $book_id = (int)$_POST['book_id'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    
    // Update stock
    $query = "UPDATE books SET stock_quantity = ?, updated_at = NOW() WHERE id = ? AND seller_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("iii", $stock_quantity, $book_id, $seller_id);
    
    if($stmt->execute()) {
        $update_message = '<div class="alert alert-success">Stock updated successfully!</div>';
    } else {
        $update_message = '<div class="alert alert-danger">Failed to update stock!</div>';
    }
}

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
                <h1 class="h2">Inventory Management</h1>
                <a href="add-book.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> Add New Book
                </a>
            </div>
            
            <?php echo $update_message; ?>
            
            <!-- Inventory Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Books</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $total_query = "SELECT COUNT(*) as total FROM books WHERE seller_id = ?";
                                        $total_stmt = $db->prepare($total_query);
                                        $total_stmt->bind_param("i", $seller_id);
                                        $total_stmt->execute();
                                        $total_result = $total_stmt->get_result();
                                        $total_row = $total_result->fetch_assoc();
                                        echo $total_row['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        In Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $in_stock_query = "SELECT COUNT(*) as total FROM books WHERE seller_id = ? AND stock_quantity > 0";
                                        $in_stock_stmt = $db->prepare($in_stock_query);
                                        $in_stock_stmt->bind_param("i", $seller_id);
                                        $in_stock_stmt->execute();
                                        $in_stock_result = $in_stock_stmt->get_result();
                                        $in_stock_row = $in_stock_result->fetch_assoc();
                                        echo $in_stock_row['total'];
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
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Low Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $low_stock_query = "SELECT COUNT(*) as total FROM books WHERE seller_id = ? AND stock_quantity > 0 AND stock_quantity < 5";
                                        $low_stock_stmt = $db->prepare($low_stock_query);
                                        $low_stock_stmt->bind_param("i", $seller_id);
                                        $low_stock_stmt->execute();
                                        $low_stock_result = $low_stock_stmt->get_result();
                                        $low_stock_row = $low_stock_result->fetch_assoc();
                                        echo $low_stock_row['total'];
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

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Out of Stock</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php 
                                        $out_stock_query = "SELECT COUNT(*) as total FROM books WHERE seller_id = ? AND stock_quantity = 0";
                                        $out_stock_stmt = $db->prepare($out_stock_query);
                                        $out_stock_stmt->bind_param("i", $seller_id);
                                        $out_stock_stmt->execute();
                                        $out_stock_result = $out_stock_stmt->get_result();
                                        $out_stock_row = $out_stock_result->fetch_assoc();
                                        echo $out_stock_row['total'];
                                        ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <a href="inventory.php" class="btn <?php echo empty($filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    All Books
                                </a>
                                <a href="inventory.php?filter=low_stock" class="btn <?php echo ($filter === 'low_stock') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Low Stock
                                </a>
                                <a href="inventory.php?filter=out_of_stock" class="btn <?php echo ($filter === 'out_of_stock') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                    Out of Stock
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <form action="" method="GET" class="row g-3">
                        <?php if(!empty($filter)): ?>
                            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                        <?php endif; ?>
                        
                        <div class="col-md-5">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Title, author, or ISBN">
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="0">All Categories</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                            <a href="inventory.php<?php echo !empty($filter) ? '?filter=' . $filter : ''; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Inventory List -->
            <div class="card">
                <div class="card-body">
                    <?php if(empty($books)): ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-data.svg" alt="No Books" style="max-width: 200px;" class="img-fluid mb-3">
                            <h4>No books found</h4>
                            <p class="text-muted">Try adjusting your filters or add new books</p>
                            <a href="add-book.php" class="btn btn-primary mt-2">
                                <i class="fas fa-plus-circle me-1"></i> Add New Book
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Book</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Current Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($books as $book): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="../uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                                         class="img-thumbnail me-2" style="width: 50px; height: 70px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($book['title']); ?></div>
                                                        <div class="small text-muted">By <?php echo htmlspecialchars($book['author']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['category_name'] ?? 'N/A'); ?></td>
                                            <td>$<?php echo number_format($book['price'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php echo ($book['stock_quantity'] <= 0) ? 'bg-danger' : (($book['stock_quantity'] < 5) ? 'bg-warning text-dark' : 'bg-success'); ?>">
                                                    <?php echo $book['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($book['approval_status'] == 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php elseif($book['approval_status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                <?php elseif($book['approval_status'] == 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateStockModal<?php echo $book['id']; ?>">
                                                    <i class="fas fa-edit"></i> Update Stock
                                                </button>
                                                <a href="edit-book.php?id=<?php echo $book['id']; ?>" 
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-pencil-alt"></i> Edit
                                                </a>
                                                
                                                <!-- Update Stock Modal -->
                                                <div class="modal fade" id="updateStockModal<?php echo $book['id']; ?>" 
                                                     tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Update Stock</h5>
                                                                <button type="button" class="btn-close" 
                                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="post">
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label for="book_title<?php echo $book['id']; ?>" class="form-label">Book</label>
                                                                        <input type="text" class="form-control" id="book_title<?php echo $book['id']; ?>" 
                                                                               value="<?php echo htmlspecialchars($book['title']); ?>" readonly>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label for="stock_quantity<?php echo $book['id']; ?>" class="form-label">
                                                                            Stock Quantity
                                                                        </label>
                                                                        <input type="number" class="form-control" id="stock_quantity<?php echo $book['id']; ?>" 
                                                                               name="stock_quantity" min="0" value="<?php echo $book['stock_quantity']; ?>" required>
                                                                    </div>
                                                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="update_stock" class="btn btn-primary">Update Stock</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&filter=<?php echo $filter; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&filter=<?php echo $filter; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once 'includes/seller-footer.php'; ?>