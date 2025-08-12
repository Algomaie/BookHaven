<?php
// categories.php
// // Start session
// session_start();

// Include config files
require_once 'config/config.php';
require_once 'config/database.php';

// Include required models
require_once 'models/Category.php';
require_once 'models/Book.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$categoryModel = new Category($db);
$bookModel = new Book($db);

// Get all categories
$categories = $categoryModel->getCategories();

// Group categories by parent/child relationship
$parentCategories = [];
$childCategories = [];

foreach ($categories as $category) {
    if ($category['parent_id'] === null || $category['parent_id'] === 0) {
        $parentCategories[] = $category;
    } else {
        if (!isset($childCategories[$category['parent_id']])) {
            $childCategories[$category['parent_id']] = [];
        }
        $childCategories[$category['parent_id']][] = $category;
    }
}

// Get popular categories for sidebar
$popularCategories = $categoryModel->getPopularCategories(5);

// Check if a specific category is selected
$selectedCategoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$selectedCategory = null;
$subcategories = [];
$categoryBooks = [];

if ($selectedCategoryId > 0) {
    // Get category details
    $categoryModel->id = $selectedCategoryId;
    $selectedCategory = $categoryModel->getCategory();
    
    if ($selectedCategory) {
        // Get subcategories if any
        $subcategories = $categoryModel->getSubcategories();
        
        // Get books for the selected category
        $bookModel->category_id = $selectedCategoryId;
        $categoryBooks = $bookModel->getBooksByCategory();
    }
}

// Include header
include_once 'views/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Left Sidebar (Categories) -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Categories</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="categories.php" class="text-decoration-none text-dark">All Categories</a>
                            <span class="badge bg-primary rounded-pill"><?php echo count($categories); ?></span>
                        </li>
                        <?php foreach ($parentCategories as $cat): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center <?php echo ($selectedCategoryId == $cat['id']) ? 'active bg-light' : ''; ?>">
                                <a href="categories.php?id=<?php echo $cat['id']; ?>" class="text-decoration-none <?php echo ($selectedCategoryId == $cat['id']) ? 'text-primary fw-bold' : 'text-dark'; ?>">
                                    <i class="<?php echo $cat['icon']; ?> me-2"></i><?php echo $cat['name']; ?>
                                </a>
                                <span class="badge bg-primary rounded-pill"><?php echo $cat['book_count']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Popular Categories -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Popular Categories</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($popularCategories as $category): ?>
                        <div class="mb-3">
                            <a href="categories.php?id=<?php echo $category['id']; ?>" class="d-flex justify-content-between align-items-center text-decoration-none">
                                <div>
                                    <i class="<?php echo $category['icon']; ?> me-2 text-primary"></i>
                                    <span class="text-dark"><?php echo $category['name']; ?></span>
                                </div>
                                <span class="badge bg-secondary rounded-pill"><?php echo $category['book_count']; ?> books</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if ($selectedCategory): ?>
                <!-- Category Details -->
                <div class="card mb-4">
                    <div class="card-body">
                        <nav aria-label="breadcrumb" class="mb-3">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="categories.php">Categories</a></li>
                                <?php if (isset($selectedCategory['parent_id']) && $selectedCategory['parent_id'] > 0): 
                                    // Get parent category name
                                    foreach ($parentCategories as $parent) {
                                        if ($parent['id'] == $selectedCategory['parent_id']) {
                                            echo '<li class="breadcrumb-item"><a href="categories.php?id=' . $parent['id'] . '">' . $parent['name'] . '</a></li>';
                                            break;
                                        }
                                    }
                                endif; ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $selectedCategory['name']; ?></li>
                            </ol>
                        </nav>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="mb-2 display-6">
                                    <i class="<?php echo $selectedCategory['icon']; ?> me-2 text-primary"></i>
                                    <?php echo $selectedCategory['name']; ?>
                                </h1>
                                <p class="text-muted"><?php echo $selectedCategory['description']; ?></p>
                            </div>
                            <div class="text-center">
                                <div class="badge bg-primary p-2 fs-6 mb-2"><?php echo $selectedCategory['book_count']; ?> Books</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($subcategories)): ?>
                    <!-- Subcategories -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Subcategories</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($subcategories as $subcat): ?>
                                    <div class="col-md-4 col-6 mb-3">
                                        <a href="categories.php?id=<?php echo $subcat['id']; ?>" class="card h-100 text-decoration-none">
                                            <div class="card-body text-center">
                                                <i class="<?php echo $subcat['icon']; ?> fa-2x mb-3 text-primary"></i>
                                                <h5 class="card-title text-dark"><?php echo $subcat['name']; ?></h5>
                                                <p class="card-text text-muted mb-0"><?php echo $subcat['book_count']; ?> Books</p>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Books in this Category -->
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>Books in <?php echo $selectedCategory['name']; ?></h5>
                        
                        <div class="d-flex align-items-center">
                            <label for="sort-books" class="me-2">Sort by:</label>
                            <select id="sort-books" class="form-select form-select-sm" style="width: auto;">
                                <option value="newest">Newest</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="bestseller">Bestsellers</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categoryBooks)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>No books found in this category.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($categoryBooks as $book): ?>
                                    <div class="col-lg-3 col-md-4 col-6 mb-4">
                                        <div class="card h-100 book-card">
                                            <div class="position-relative">
                                                <a href="book-details.php?id=<?php echo $book['id']; ?>">
                                                    <img src="uploads/book_covers/<?php echo $book['cover_image']; ?>" class="card-img-top" alt="<?php echo $book['title']; ?>">
                                                </a>
                                                <?php if ($book['discount_percent'] > 0): ?>
                                                    <div class="position-absolute top-0 start-0 m-2">
                                                        <span class="badge bg-danger">
                                                            <?php echo number_format($book['discount_percent']); ?>% OFF
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($book['is_featured']): ?>
                                                    <div class="position-absolute top-0 end-0 m-2">
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-star me-1"></i> Featured
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="text-decoration-none text-dark">
                                                        <?php 
                                                        // Limit title length
                                                        echo (strlen($book['title']) > 40) ? substr($book['title'], 0, 40) . '...' : $book['title']; 
                                                        ?>
                                                    </a>
                                                </h5>
                                                <p class="card-text text-muted small">By <?php echo $book['author']; ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <?php if ($book['discount_percent'] > 0): 
                                                        $discounted_price = $book['price'] * (1 - $book['discount_percent'] / 100);
                                                    ?>
                                                        <span class="fw-bold text-primary me-2">$<?php echo number_format($discounted_price, 2); ?></span>
                                                        <span class="text-muted text-decoration-line-through small">$<?php echo number_format($book['price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span class="fw-bold text-primary">$<?php echo number_format($book['price'], 2); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-white d-flex justify-content-between">
                                                <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <button class="btn btn-sm btn-primary add-to-cart" data-book-id="<?php echo $book['id']; ?>">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            
            <?php else: ?>
                <!-- All Categories -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-th-large me-2"></i>Browse All Categories</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($parentCategories as $category): ?>
                                <div class="col-md-4 col-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="<?php echo $category['icon']; ?> fa-3x mb-3 text-primary"></i>
                                            <h4 class="card-title"><?php echo $category['name']; ?></h4>
                                            <p class="text-muted mb-3"><?php echo $category['book_count']; ?> Books</p>
                                            
                                            <?php if (isset($childCategories[$category['id']])): ?>
                                                <div class="small mb-3">
                                                    <?php foreach ($childCategories[$category['id']] as $index => $child): 
                                                        if ($index < 3): // Show only first 3 subcategories
                                                    ?>
                                                        <a href="categories.php?id=<?php echo $child['id']; ?>" class="badge bg-light text-dark me-1 mb-1 d-inline-block">
                                                            <?php echo $child['name']; ?> (<?php echo $child['book_count']; ?>)
                                                        </a>
                                                    <?php 
                                                        endif;
                                                    endforeach; 
                                                    
                                                    if (count($childCategories[$category['id']]) > 3): // Show "more" badge if more than 3 subcategories
                                                    ?>
                                                        <span class="badge bg-secondary me-1 mb-1">
                                                            +<?php echo count($childCategories[$category['id']]) - 3; ?> more
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <a href="categories.php?id=<?php echo $category['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                Browse Books
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Featured Categories -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Featured Categories</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            // Get top 3 categories with most books
                            $featuredCategories = array_slice($popularCategories, 0, 3);
                            
                            foreach ($featuredCategories as $featCategory):
                                // Get category model to fetch books
                                $bookModel->category_id = $featCategory['id'];
                                $categoryPreviewBooks = $bookModel->getBooksByCategory(4); // Get 4 books for preview
                            ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <i class="<?php echo $featCategory['icon']; ?> me-2"></i>
                                                    <?php echo $featCategory['name']; ?>
                                                </h5>
                                                <span class="badge bg-primary"><?php echo $featCategory['book_count']; ?></span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <?php if (empty($categoryPreviewBooks)): ?>
                                                <p class="text-muted mb-0">No books available.</p>
                                            <?php else: ?>
                                                <div class="row g-2">
                                                    <?php foreach ($categoryPreviewBooks as $previewBook): ?>
                                                        <div class="col-6">
                                                            <a href="book-details.php?id=<?php echo $previewBook['id']; ?>" class="text-decoration-none">
                                                                <img src="uploads/book_covers/<?php echo $previewBook['cover_image']; ?>" alt="<?php echo $previewBook['title']; ?>" class="img-fluid rounded mb-1">
                                                                <div class="small text-truncate text-dark"><?php echo $previewBook['title']; ?></div>
                                                                <div class="small text-primary">$<?php echo number_format($previewBook['price'], 2); ?></div>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-white text-center">
                                            <a href="categories.php?id=<?php echo $featCategory['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                View All Books
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add to Cart Functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add to cart buttons
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bookId = this.getAttribute('data-book-id');
                
                // Show loading state
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                
                // Add to cart via AJAX
                fetch('ajax/add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `book_id=${bookId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in header
                        document.getElementById('cart-count').textContent = data.cartCount;
                        
                        // Show success state
                        this.innerHTML = '<i class="fas fa-check"></i>';
                        
                        // Reset button after delay
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                            this.disabled = false;
                        }, 2000);
                        
                        // Show notification
                        alert('Book added to cart successfully!');
                    } else {
                        // Show error
                        this.innerHTML = originalHTML;
                        this.disabled = false;
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                    alert('An error occurred. Please try again.');
                });
            });
        });
        
        // Sort books functionality
        const sortSelect = document.getElementById('sort-books');
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const sortValue = this.value;
                const bookCards = document.querySelectorAll('.book-card');
                const bookCardsArray = Array.from(bookCards);
                const bookContainer = bookCards[0].parentNode.parentNode;
                
                // Sort books based on selected option
                bookCardsArray.sort((a, b) => {
                    if (sortValue === 'newest') {
                        // Default order is already by newest
                        return 0;
                    } else if (sortValue === 'price-low') {
                        const priceA = parseFloat(a.querySelector('.text-primary').textContent.replace('$', ''));
                        const priceB = parseFloat(b.querySelector('.text-primary').textContent.replace('$', ''));
                        return priceA - priceB;
                    } else if (sortValue === 'price-high') {
                        const priceA = parseFloat(a.querySelector('.text-primary').textContent.replace('$', ''));
                        const priceB = parseFloat(b.querySelector('.text-primary').textContent.replace('$', ''));
                        return priceB - priceA;
                    } else if (sortValue === 'bestseller') {
                        // In real implementation, this would use actual bestseller data
                        // Here we're just randomizing for demo purposes
                        return 0.5 - Math.random();
                    }
                    return 0;
                });
                
                // Clear and re-append sorted books
                bookCardsArray.forEach(card => {
                    const cardWrapper = card.parentNode;
                    bookContainer.appendChild(cardWrapper);
                });
            });
        }
    });
</script>

<?php
// Include footer
include_once 'views/includes/footer.php';
?>