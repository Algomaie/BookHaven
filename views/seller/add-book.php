<?php
// views/seller/add-book.php
// Start session
session_start();

// Include header and sidebar
require_once 'includes/seller-header.php';


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
    
    // Validate and upload cover image
    $cover_image = 'default-book.jpg'; // Default image
    
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
if(empty($errors)) {
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = __DIR__ . "/../../uploads/books/$new_filename";
            
            if(move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
                $cover_image = $new_filename;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    
    // If no errors, add book to database
    if (empty($errors)) {
        // Set book properties
        $bookModel->title = htmlspecialchars(trim($_POST['title']));
        $bookModel->author = htmlspecialchars(trim($_POST['author']));
        $bookModel->description = htmlspecialchars(trim($_POST['description']));
        $bookModel->isbn = !empty($_POST['isbn']) ? htmlspecialchars(trim($_POST['isbn'])) : null;
        $bookModel->price = floatval($_POST['price']);
        $bookModel->discount_percent = !empty($_POST['discount_percent']) ? floatval($_POST['discount_percent']) : 0;
        $bookModel->category_id = intval($_POST['category_id']);
        $bookModel->seller_id = $_SESSION['user_id'];
        $bookModel->publisher = !empty($_POST['publisher']) ? htmlspecialchars(trim($_POST['publisher'])) : null;
        $bookModel->publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
        $bookModel->language = !empty($_POST['language']) ? htmlspecialchars(trim($_POST['language'])) : 'English';
        $bookModel->page_count = !empty($_POST['page_count']) ? intval($_POST['page_count']) : null;
        $bookModel->stock_quantity = intval($_POST['stock_quantity']);
        $bookModel->cover_image = $cover_image;
        $bookModel->approval_status = 'pending'; // All new books start as pending
        
        // Create book
        if ($bookModel->create()) {
            $success = true;
        } else {
            $errors[] = 'Failed to add book. Please try again.';
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
                <h1 class="h2">Add New Book</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="books.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Books
                    </a>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Book Added Successfully!</h4>
                    <p>Your book has been submitted and is now awaiting approval from our administrators. You'll be notified once it's approved and listed on the marketplace.</p>
                    <hr>
                    <p class="mb-0">
                        <a href="books.php" class="alert-link">View all your books</a> or 
                        <a href="add-book.php" class="alert-link">add another book</a>.
                    </p>
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
            
            <form id="add-book-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                        <div class="invalid-feedback">Please enter a book title.</div>
                                    </div>
                                    
                                    <!-- Author -->
                                    <div class="col-md-6">
                                        <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="author" name="author" value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>" required>
                                        <div class="invalid-feedback">Please enter the author's name.</div>
                                    </div>
                                    
                                    <!-- ISBN -->
                                    <div class="col-md-6">
                                        <label for="isbn" class="form-label">ISBN</label>
                                        <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>" placeholder="Optional">
                                        <div class="form-text">International Standard Book Number (optional)</div>
                                    </div>
                                    
                                    <!-- Category -->
                                    <div class="col-md-6">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select a category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
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
                                            <option value="English" <?php echo (!isset($_POST['language']) || $_POST['language'] == 'English') ? 'selected' : ''; ?>>English</option>
                                            <option value="Spanish" <?php echo (isset($_POST['language']) && $_POST['language'] == 'Spanish') ? 'selected' : ''; ?>>Spanish</option>
                                            <option value="French" <?php echo (isset($_POST['language']) && $_POST['language'] == 'French') ? 'selected' : ''; ?>>French</option>
                                            <option value="German" <?php echo (isset($_POST['language']) && $_POST['language'] == 'German') ? 'selected' : ''; ?>>German</option>
                                            <option value="Chinese" <?php echo (isset($_POST['language']) && $_POST['language'] == 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                                            <option value="Japanese" <?php echo (isset($_POST['language']) && $_POST['language'] == 'Japanese') ? 'selected' : ''; ?>>Japanese</option>
                                            <option value="Other" <?php echo (isset($_POST['language']) && $_POST['language'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Publisher -->
                                    <div class="col-md-6">
                                        <label for="publisher" class="form-label">Publisher</label>
                                        <input type="text" class="form-control" id="publisher" name="publisher" value="<?php echo isset($_POST['publisher']) ? htmlspecialchars($_POST['publisher']) : ''; ?>" placeholder="Optional">
                                    </div>
                                    
                                    <!-- Publication Date -->
                                    <div class="col-md-6">
                                        <label for="publication_date" class="form-label">Publication Date</label>
                                        <input type="date" class="form-control" id="publication_date" name="publication_date" value="<?php echo isset($_POST['publication_date']) ? $_POST['publication_date'] : ''; ?>">
                                    </div>
                                    
                                    <!-- Page Count -->
                                    <div class="col-md-6">
                                        <label for="page_count" class="form-label">Page Count</label>
                                        <input type="number" class="form-control" id="page_count" name="page_count" min="1" value="<?php echo isset($_POST['page_count']) ? intval($_POST['page_count']) : ''; ?>" placeholder="Optional">
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="col-md-6">
                                        <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid price.</div>
                                    </div>
                                    
                                    <!-- Discount -->
                                    <div class="col-md-6">
                                        <label for="discount_percent" class="form-label">Discount (%)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="discount_percent" name="discount_percent" min="0" max="99" value="<?php echo isset($_POST['discount_percent']) ? htmlspecialchars($_POST['discount_percent']) : '0'; ?>">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div id="final-price" class="form-text">
                                            Final price: $<?php 
                                                $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
                                                $discount = isset($_POST['discount_percent']) ? floatval($_POST['discount_percent']) : 0;
                                                echo number_format($price * (1 - $discount/100), 2); 
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Quantity -->
                                    <div class="col-md-6">
                                        <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo isset($_POST['stock_quantity']) ? intval($_POST['stock_quantity']) : '1'; ?>" required>
                                        <div class="invalid-feedback">Please enter a valid stock quantity.</div>
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="col-md-12">
                                        <label for="description" class="form-label">Book Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="6"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
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
                                    <img id="cover-preview" src="../assets/images/default-book-cover.jpg" alt="Book Cover Preview" class="img-fluid img-thumbnail" style="max-height: 250px;">
                                </div>
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Upload Cover Image</label>
                                    <input class="form-control" type="file" id="cover_image" name="cover_image" accept="image/*">
                                    <div class="form-text">Recommended size: 600x900 pixels (JPG, PNG, or GIF, max 5MB)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Publishing Info -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Publishing Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-exclamation-circle me-2"></i>Approval Process</h6>
                                    <p class="small mb-0">All new books require admin approval before they appear on the marketplace. This process usually takes 24-48 hours.</p>
                                </div>
                                <div class="d-grid mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Submit Book for Approval
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
        const form = document.getElementById('add-book-form');
        
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