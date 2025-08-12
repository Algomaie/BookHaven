<?php
// views/seller/edit-book.php
// Start session
session_start();

// Include header and sidebar
require_once 'includes/seller-header.php';

// Include config files

// Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/Book.php';
require_once '../../models/Category.php';
// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$categoryModel = new Category($db);
$bookModel = new Book($db);

// Get all categories
$categories = $categoryModel->getCategories();

// Check if book ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: books.php');
    exit();
}

$book_id = intval($_GET['id']);
$seller_id = $_SESSION['user_id'];

// Get book details
$bookModel->id = $book_id;
$bookModel->seller_id = $seller_id; // Ensure only the seller's own books can be edited
$book = $bookModel->getSingleBook();

// If book not found or doesn't belong to the seller, redirect
if (!$book || $book['seller_id'] != $seller_id) {
    header('Location: books.php');
    exit();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    if (empty($_POST['title'])) {
        $errors[] = 'Book title is required.';
    }
    
    if (empty($_POST['author'])) {
        $errors[] = 'Author name is required.';
    }
    
    if (empty($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] <= 0) {
        $errors[] = 'Valid price is required.';
    }
    
    if (empty($_POST['category_id'])) {
        $errors[] = 'Category is required.';
    }
    
    if (empty($_POST['stock_quantity']) || !is_numeric($_POST['stock_quantity']) || $_POST['stock_quantity'] < 0) {
        $errors[] = 'Valid stock quantity is required.';
    }
    
    // Handle cover image upload (if a new one is provided)
    $cover_image = $book['cover_image']; // Default to current image
    
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['cover_image']['tmp_name'];
        $file_name = $_FILES['cover_image']['name'];
        $file_size = $_FILES['cover_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check file size (max 5MB)
        if ($file_size > 5242880) {
            $errors[] = 'Cover image must be less than 5MB.';
        }
        
        // Check file extension
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = 'Cover image must be JPG, JPEG, PNG, or GIF.';
        }
        
        // Upload file if no errors
        if (empty($errors)) {
            $new_file_name = uniqid('book_') . '.' . $file_ext;
            $upload_path = '../uploads/book_covers/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $cover_image = $new_file_name;
                
                // Delete old cover image if it's not the default
                if ($book['cover_image'] != 'default-book.jpg' && file_exists('../uploads/book_covers/' . $book['cover_image'])) {
                    unlink('../uploads/book_covers/' . $book['cover_image']);
                }
            } else {
                $errors[] = 'Failed to upload cover image.';
            }
        }
    }
    
    // If no errors, update book in database
    if (empty($errors)) {
        // Set book properties
        $bookModel->id = $book_id;
        $bookModel->title = htmlspecialchars(trim($_POST['title']));
        $bookModel->author = htmlspecialchars(trim($_POST['author']));
        $bookModel->description = htmlspecialchars(trim($_POST['description']));
        $bookModel->isbn = !empty($_POST['isbn']) ? htmlspecialchars(trim($_POST['isbn'])) : null;
        $bookModel->price = floatval($_POST['price']);
        $bookModel->discount_percent = !empty($_POST['discount_percent']) ? floatval($_POST['discount_percent']) : 0;
        $bookModel->category_id = intval($_POST['category_id']);
        $bookModel->seller_id = $seller_id;
        $bookModel->publisher = !empty($_POST['publisher']) ? htmlspecialchars(trim($_POST['publisher'])) : null;
        $bookModel->publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
        $bookModel->language = !empty($_POST['language']) ? htmlspecialchars(trim($_POST['language'])) : 'English';
        $bookModel->page_count = !empty($_POST['page_count']) ? intval($_POST['page_count']) : null;
        $bookModel->stock_quantity = intval($_POST['stock_quantity']);
        $bookModel->cover_image = $cover_image;
        
        // Check if significant details were changed
        $significantChanges = ($book['title'] != $bookModel->title || 
                             $book['author'] != $bookModel->author || 
                             $book['description'] != $bookModel->description || 
                             $book['category_id'] != $bookModel->category_id);
        
        // Reset approval status to pending if significant changes were made
        if ($significantChanges && $book['approval_status'] == 'approved') {
            $bookModel->approval_status = 'pending';
        }
        
        // Update book
        if ($bookModel->update()) {
            $success = true;
            
            // Refresh book data after update
            $book = $bookModel->getSingleBook();
        } else {
            $errors[] = 'Failed to update book. Please try again.';
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Seller Sidebar -->
        <?php include_once 'includes/seller-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Book</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="../book-details.php?id=<?php echo $book_id; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-eye me-1"></i> View on Site
                        </a>
                    </div>
                    <a href="books.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Books
                    </a>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Book Updated Successfully!</h4>
                    <p>Your changes have been saved.</p>
                    <?php if ($book['approval_status'] == 'pending'): ?>
                        <hr>
                        <p class="mb-0">
                            <i class="fas fa-info-circle me-1"></i> Due to significant changes, your book requires admin review before it appears on the marketplace again.
                        </p>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error!</h4>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Book Status Card -->
            <div class="card mb-4 
                <?php if ($book['approval_status'] == 'approved'): ?>
                    border-success
                <?php elseif ($book['approval_status'] == 'pending'): ?>
                    border-warning
                <?php else: ?>
                    border-danger
                <?php endif; ?>">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>
                                <span class="badge 
                                    <?php if ($book['approval_status'] == 'approved'): ?>
                                        bg-success
                                    <?php elseif ($book['approval_status'] == 'pending'): ?>
                                        bg-warning text-dark
                                    <?php else: ?>
                                        bg-danger
                                    <?php endif; ?> me-2">
                                    <?php echo ucfirst($book['approval_status']); ?>
                                </span>
                                Book Status
                            </h5>
                            <p class="mb-0">
                                <?php if ($book['approval_status'] == 'approved'): ?>
                                    This book is approved and visible to customers.
                                <?php elseif ($book['approval_status'] == 'pending'): ?>
                                    This book is awaiting admin approval before it appears on the marketplace.
                                <?php else: ?>
                                    This book has been rejected by admins.
                                    <?php if (!empty($book['rejection_reason'])): ?>
                                        <br><strong>Reason:</strong> <?php echo htmlspecialchars($book['rejection_reason']); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-md-end">
                                <div class="me-3">
                                    <h6 class="mb-1">Listed On</h6>
                                    <p class="mb-0"><?php echo date('M d, Y', strtotime($book['created_at'])); ?></p>
                                </div>
                                <div>
                                    <h6 class="mb-1">Last Updated</h6>
                                    <p class="mb-0"><?php echo date('M d, Y', strtotime($book['updated_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="edit-book-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo $book_id; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
                    <!-- Left Column: Book Details -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-book me-2"></i>Book Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- Title -->
                                    <div class="col-md-12">
                                        <label for="title" class="form-label">Book Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                                        <div class="invalid-feedback">Please enter a book title.</div>
                                    </div>
                                    
                                    <!-- Author -->
                                    <div class="col-md-6">
                                        <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                                        <div class="invalid-feedback">Please enter the author's name.</div>
                                    </div>
                                    
                                    <!-- ISBN -->
                                    <div class="col-md-6">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>" placeholder="Optional">
                                        <div class="form-text">International Standard Book Number (optional)</div>
                                    </div>
                                    
                                    <!-- Category -->
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select a category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo ($book['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $category['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select a category.</div>
                                    </div>
                                    
                                    <!-- Language -->
                                    <div class="col-md-6">
                                        <label for="language" class="form-label">Language</label>
                                        <select class="form-select" id="language" name="language">
                                            <option value="English" <?php echo ($book['language'] == 'English' || empty($book['language'])) ? 'selected' : ''; ?>>English</option>
                                            <option value="Spanish" <?php echo ($book['language'] == 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                                            <option value="French" <?php echo ($book['language'] == 'French') ? 'selected' : ''; ?>>French</option>
                                            <option value="German" <?php echo ($book['language'] == 'German') ? 'selected' : ''; ?>>German</option>
                                            <option value="Chinese" <?php echo ($book['language'] == 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                                            <option value="Japanese" <?php echo ($book['language'] == 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                                            <option value="Other" <?php echo ($book['language'] != 'English' && $book['language'] != 'Spanish' && $book['language'] != 'French' && $book['language'] != 'German' && $book['language'] != 'Chinese' && $book['language'] != 'Japanese' && !empty($book['language'])) ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Publisher -->
                                    <div class="col-md-6">
                                        <label for="publisher" class="form-label">Publisher</label>
                                        <input type="text" class="form-control" id="publisher" name="publisher" value="<?php echo htmlspecialchars($book['publisher'] ?? ''); ?>" placeholder="Optional">
                                    </div>
                                    
                                    <!-- Publication Date -->
                                    <div class="col-md-6">
                                        <label for="publication_date" class="form-label">Publication Date</label>
                                        <input type="date" class="form-control" id="publication_date" name="publication_date" value="<?php echo $book['publication_date'] ?? ''; ?>">
                                    </div>
                                    
                                    <!-- Page Count -->
                                    <div class="col-md-6">
                                        <label for="page_count" class="form-label">Page Count</label>
                                        <input type="number" class="form-control" id="page_count" name="page_count" min="1" value="<?php echo intval($book['page_count'] ?? 0); ?>" placeholder="Optional">
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="col-md-6">
                                        <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" value="<?php echo number_format((float)$book['price'], 2, '.', ''); ?>" required>
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid price.</div>
                                    </div>
                                    
                                    <!-- Discount -->
                                    <div class="col-md-6">
                                        <label for="discount_percent" class="form-label">Discount (%)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="discount_percent" name="discount_percent" min="0" max="99" value="<?php echo intval($book['discount_percent'] ?? 0); ?>">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div id="final-price" class="form-text">
                                            Final price: $<?php 
                                                $price = (float)$book['price'];
                                                $discount = intval($book['discount_percent'] ?? 0);
                                                echo number_format($price * (1 - $discount/100), 2); 
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-6">
                                        <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo intval($book['stock_quantity']); ?>" required>
                                        <div class="invalid-feedback">Please enter a valid stock quantity.</div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="col-md-12">
                                        <label for="description" class="form-label">Book Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="6"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
                                        <div class="form-text">A detailed description will help customers better understand your book.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Cover Image & Submit -->
                    <div class="col-lg-4">
                        <!-- Cover Image Upload -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-image me-2"></i>Cover Image</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img id="cover-preview" src="../uploads/book_covers/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover Preview" class="img-fluid img-thumbnail" style="max-height: 250px;">
                                </div>
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Change Cover Image</label>
                                    <input class="form-control" type="file" id="cover_image" name="cover_image" accept="image/*">
                                    <div class="form-text">Recommended size: 600x900 pixels (JPG, PNG, or GIF, max 5MB)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sales Info -->
                        <?php if ($book['approval_status'] == 'approved'): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Sales Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6 border-end">
                                        <h3 class="mb-1">0</h3>
                                        <p class="text-muted mb-0">Copies Sold</p>
                                    </div>
                                    <div class="col-6">
                                        <h3 class="mb-1">0</h3>
                                        <p class="text-muted mb-0">Revenue</p>
                                    </div>
                                </div>
                                <a href="book-sales.php?id=<?php echo $book_id; ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">
                                    <i class="fas fa-chart-bar me-1"></i> View Detailed Report
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Update Button -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="alert alert-info small mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php if ($book['approval_status'] == 'approved'): ?>
                                        Significant changes may require admin re-approval before being visible to customers.
                                    <?php elseif ($book['approval_status'] == 'pending'): ?>
                                        Your book is currently awaiting approval. Updates will extend the review process.
                                    <?php else: ?>
                                        Your book was rejected. Making changes and updating may help address admin concerns.
                                    <?php endif; ?>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Book
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.getElementById('edit-book-form');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
        
        // Cover image preview
        const coverInput = document.getElementById('cover_image');
        const coverPreview = document.getElementById('cover-preview');
        
        coverInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    coverPreview.src = e.target.result;
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Calculate final price when price or discount changes
        const priceInput = document.getElementById('price');
        const discountInput = document.getElementById('discount_percent');
        const finalPriceDisplay = document.getElementById('final-price');
        
        function updateFinalPrice() {
            const price = parseFloat(priceInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;
            const finalPrice = price * (1 - discount/100);
            
            finalPriceDisplay.textContent = `Final price: $${finalPrice.toFixed(2)}`;
        }
        
        priceInput.addEventListener('input', updateFinalPrice);
        discountInput.addEventListener('input', updateFinalPrice);
    });
</script>

<?php include_once 'includes/seller-footer.php'; ?>