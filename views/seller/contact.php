<?php
// views/seller/contact.php
session_start();

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: /login.php');
    exit;
}

// Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/User.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$userModel = new User($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];
$userModel->id = $seller_id;

// Get seller details
$seller = $userModel->getUser();

// Handle form submission
$sent_message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $category = $_POST['category'] ?? '';
    
    // Validate inputs
    if(!empty($subject) && !empty($message) && !empty($category)) {
        // In a real application, you would save the support ticket to the database
        // and perhaps send an email notification
        
        // For this example, just show a success message
        $sent_message = '<div class="alert alert-success">Your message has been sent! We\'ll get back to you as soon as possible.</div>';
    } else {
        $sent_message = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    }
}

// Include seller header
include_once 'seller-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Seller Sidebar -->
        <?php include_once 'seller-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Contact Support</h1>
                <a href="help.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Help
                </a>
            </div>
            
            <?php echo $sent_message; ?>
            
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Send a Message</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($seller['username']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Your Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($seller['email']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select a category</option>
                                        <option value="account">Account Issues</option>
                                        <option value="orders">Orders & Shipping</option>
                                        <option value="payments">Payments & Payouts</option>
                                        <option value="products">Book Listings</option>
                                        <option value="technical">Technical Support</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                    <small class="form-text text-muted">Please provide as much detail as possible so we can best assist you.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="attachment" class="form-label">Attachment (Optional)</label>
                                    <input type="file" class="form-control" id="attachment" name="attachment">
                                    <small class="form-text text-muted">Max file size: 5MB. Supported formats: JPG, PNG, PDF, DOC, DOCX.</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <i class="fas fa-envelope fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Email</h6>
                                    <p class="mb-0">seller.support@bookstore.com</p>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-3">
                                <div class="me-3">
                                    <i class="fas fa-phone-alt fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Phone</h6>
                                    <p class="mb-0">+1 (555) 123-4567</p>
                                    <p class="small text-muted mb-0">Monday-Friday, 9am-6pm EST</p>
                                </div>
                            </div>
                            
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold">Address</h6>
                                    <p class="mb-0">
                                        123 Book Street<br>
                                        New York, NY 10001<br>
                                        United States
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Support Hours</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Monday - Friday</span>
                                    <span>9:00 AM - 6:00 PM EST</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Saturday</span>
                                    <span>10:00 AM - 4:00 PM EST</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Sunday</span>
                                    <span>Closed</span>
                                </li>
                            </ul>
                            <div class="mt-3">
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    Average response time: Within 24 hours
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once 'seller-footer.php'; ?>