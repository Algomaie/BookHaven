<?php
// views/seller/settings.php
session_start();

// Check if user is logged in and is a seller
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: ../auth/login.php');
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

// Get seller preferences (example)
$preferences = [
    'notifications_email' => true,
    'notifications_order' => true,
    'notifications_review' => true,
    'privacy_show_email' => false,
    'privacy_show_phone' => false,
    'theme' => 'light',
    'language' => 'en'
];

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
                <h1 class="h2">Account Settings</h1>
            </div>
            
            <!-- Settings Tabs -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" 
                            type="button" role="tab" aria-controls="notifications" aria-selected="true">
                        Notifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" 
                            type="button" role="tab" aria-controls="privacy" aria-selected="false">
                        Privacy
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="display-tab" data-bs-toggle="tab" data-bs-target="#display" 
                            type="button" role="tab" aria-controls="display" aria-selected="false">
                        Display
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" 
                            type="button" role="tab" aria-controls="payment" aria-selected="false">
                        Payment Methods
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" 
                            type="button" role="tab" aria-controls="shipping" aria-selected="false">
                        Shipping Settings
                    </button>
                </li>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content" id="settingsTabsContent">
                <!-- Notifications Settings -->
                <div class="tab-pane fade show active" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <h6 class="fw-bold">Email Notifications</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="notifications_email" name="notifications_email" 
                                               <?php echo $preferences['notifications_email'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notifications_email">
                                            Receive email notifications
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            General notifications about your account and activity.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Order Notifications</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="notifications_order" name="notifications_order" 
                                               <?php echo $preferences['notifications_order'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notifications_order">
                                            Receive order notifications
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Notifications about new orders, order updates, and shipping confirmations.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Review Notifications</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="notifications_review" name="notifications_review" 
                                               <?php echo $preferences['notifications_review'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notifications_review">
                                            Receive review notifications
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Notifications when customers leave reviews on your books.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Newsletter</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="notifications_newsletter" name="notifications_newsletter" checked>
                                        <label class="form-check-label" for="notifications_newsletter">
                                            Receive seller newsletter
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Monthly newsletter with seller tips, platform updates, and promotions.
                                        </small>
                                    </div>
                                </div>
                                
                                <button type="submit" name="save_notifications" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Notification Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Privacy Settings -->
                <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Privacy Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <h6 class="fw-bold">Contact Information Visibility</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="privacy_show_email" name="privacy_show_email" 
                                               <?php echo $preferences['privacy_show_email'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="privacy_show_email">
                                            Show email to customers
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Allow customers to see your email address on your seller profile.
                                        </small>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="privacy_show_phone" name="privacy_show_phone" 
                                               <?php echo $preferences['privacy_show_phone'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="privacy_show_phone">
                                            Show phone number to customers
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Allow customers to see your phone number on your seller profile.
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Data Management</h6>
                                    <p class="text-muted">Manage your data and privacy settings.</p>
                                    
                                    <div class="mb-3">
                                        <button type="button" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-download me-1"></i> Download Your Data
                                        </button>
                                        <button type="button" class="btn btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i> Delete Account
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" name="save_privacy" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Privacy Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Display Settings -->
                <div class="tab-pane fade" id="display" role="tabpanel" aria-labelledby="display-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Display Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <h6 class="fw-bold">Theme</h6>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="theme" id="theme_light" value="light" 
                                               <?php echo $preferences['theme'] === 'light' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="theme_light">Light</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="theme" id="theme_dark" value="dark" 
                                               <?php echo $preferences['theme'] === 'dark' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="theme_dark">Dark</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="theme" id="theme_system" value="system" 
                                               <?php echo $preferences['theme'] === 'system' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="theme_system">System Default</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="fw-bold">Language</h6>
                                    <select class="form-select" id="language" name="language">
                                        <option value="en" <?php echo $preferences['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                        <option value="es" <?php echo $preferences['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                        <option value="fr" <?php echo $preferences['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                        <option value="de" <?php echo $preferences['language'] === 'de' ? 'selected' : ''; ?>>German</option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="save_display" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Display Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Payment Methods</h5>
                        </div>
                        <div class="card-body">
                            <p>Manage your payment methods and payout settings.</p>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold">Payment Processor</h6>
                                <p>Connect your account to receive payments from sales.</p>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <img src="../assets/images/paypal.png" alt="PayPal" width="80">
                                    </div>
                                    <div>
                                        <div class="fw-bold">PayPal</div>
                                        <div class="text-muted">Not connected</div>
                                        <button class="btn btn-sm btn-outline-primary mt-1">Connect</button>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <img src="../assets/images/stripe.png" alt="Stripe" width="80">
                                    </div>
                                    <div>
                                        <div class="fw-bold">Stripe</div>
                                        <div class="text-muted">Not connected</div>
                                        <button class="btn btn-sm btn-outline-primary mt-1">Connect</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold">Bank Account</h6>
                                <p>Add your bank account details for direct deposits.</p>
                                
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Add Bank Account
                                </button>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold">Payout Schedule</h6>
                                <p>Choose when you want to receive your earnings.</p>
                                
                                <select class="form-select">
                                    <option selected>Weekly</option>
                                    <option>Bi-weekly</option>
                                    <option>Monthly</option>
                                </select>
                            </div>
                            
                            <button class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Payment Settings
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Settings -->
                <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Shipping Settings</h5>
                        </div>
                        <div class="card-body">
                            <p>Configure your shipping settings and options.</p>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold">Shipping Origin</h6>
                                <p>Set the location from where you ship your books.</p>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="origin_country" class="form-label">Country</label>
                                        <select class="form-select" id="origin_country">
                                            <option selected>United States</option>
                                            <option>Canada</option>
                                            <option>United Kingdom</option>
                                            <option>Australia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="origin_zip" class="form-label">ZIP/Postal Code</label>
                                        <input type="text" class="form-control" id="origin_zip" placeholder="Enter ZIP code">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold">Shipping Options</h6>
                                <p>Set up shipping methods and rates.</p>
                                
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="shipping_standard" checked>
                                            <label class="form-check-label" for="shipping_standard">
                                                <strong>Standard Shipping</strong>
                                            </label>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <input type="number" class="form-control" placeholder="Price ($)" value="5.99">
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select">
                                                    <option selected>3-5 business days</option>
                                                    <option>2-3 business days</option>
                                                    <option>1-2 business days</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="shipping_express" checked>
                                            <label class="form-check-label" for="shipping_express">
                                                <strong>Express Shipping</strong>
                                            </label>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <input type="number" class="form-control" placeholder="Price ($)" value="12.99">
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select">
                                                    <option selected>1-2 business days</option>
                                                    <option>Next day</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button class="btn btn-outline-primary mb-3">
                                    <i class="fas fa-plus-circle me-1"></i> Add Shipping Option
                                </button>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="fw-bold">Free Shipping</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="free_shipping" checked>
                                    <label class="form-check-label" for="free_shipping">
                                        Offer free shipping on orders over:
                                    </label>
                                </div>
                                <div class="input-group mb-3" style="max-width: 200px;">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" value="50">
                                </div>
                            </div>
                            
                            <button class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Shipping Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include_once 'includes/seller-footer.php'; ?>