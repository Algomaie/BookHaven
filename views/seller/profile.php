<?php
// views/seller/profile.php
session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: /login.php');
    exit;
}

// Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../models/User.php';
require_once '../../models/Book.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$userModel = new User($db);
$bookModel = new Book($db);

// Get seller ID from session
$seller_id = $_SESSION['user_id'];
$userModel->id = $seller_id;

// Get seller details
$seller = $userModel->getUser();
$seller_profile = $userModel->getSellerProfile();

// Count books
$bookModel->seller_id = $seller_id;
$total_books = $bookModel->countBooksBySeller($seller_id);

// Handle profile update
$update_message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Validate form data
    if (empty($_POST['username'])) {
        $errors[] = "Username is required";
    }
    
    if (empty($_POST['email'])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($_POST['business_name'])) {
        $errors[] = "Business name is required";
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $userModel->username = $_POST['username'];
        $userModel->email = $_POST['email'];
        $userModel->phone = $_POST['phone'] ?? '';
        
        // Update user
        if ($userModel->updateUser()) {
            // Update seller profile
            $query = "UPDATE seller_profiles 
                      SET business_name = ?, contact_email = ?, contact_phone = ?, address = ?, description = ?
                      WHERE user_id = ?";
            $stmt = $db->prepare($query);
            
            $business_name = $_POST['business_name'];
            $contact_email = $_POST['contact_email'] ?? $_POST['email'];
            $contact_phone = $_POST['contact_phone'] ?? $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $stmt->bind_param("sssssi", $business_name, $contact_email, $contact_phone, $address, $description, $seller_id);
            
            if ($stmt->execute()) {
                $update_message = '<div class="alert alert-success">Profile updated successfully!</div>';
                
                // Refresh seller data
                $seller = $userModel->getUser();
                $seller_profile = $userModel->getSellerProfile();
            } else {
                $update_message = '<div class="alert alert-danger">Error updating seller profile!</div>';
            }
        } else {
            $update_message = '<div class="alert alert-danger">Error updating user!</div>';
        }
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate password data
    if (empty($current_password)) {
        $errors[] = "Current password is required";
    }
    
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, update password
    if (empty($errors)) {
        // Verify current password
        if ($userModel->verifyPassword($current_password)) {
            // Update password
            if ($userModel->updatePassword($new_password)) {
                $update_message = '<div class="alert alert-success">Password updated successfully!</div>';
            } else {
                $update_message = '<div class="alert alert-danger">Error updating password!</div>';
            }
        } else {
            $update_message = '<div class="alert alert-danger">Current password is incorrect!</div>';
        }
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
                <h1 class="h2">Seller Profile</h1>
            </div>
            
            <?php echo $update_message; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <!-- Profile Summary Card -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <img src="<?php echo !empty($seller_profile['profile_image']) ? '../uploads/users/' . $seller_profile['profile_image'] : '../assets/images/default-profile.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($seller['username']); ?>" 
                                 class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <h5 class="card-title"><?php echo htmlspecialchars($seller['username']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($seller_profile['business_name'] ?? 'Seller'); ?></p>
                            <p class="card-text">
                                <i class="fas fa-book me-2"></i> <?php echo $total_books; ?> Books
                            </p>
                            <p class="card-text">
                                <i class="fas fa-calendar-alt me-2"></i> Member since <?php echo date('M Y', strtotime($seller['created_at'])); ?>
                            </p>
                            <div class="d-grid">
                                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#profileImageModal">
                                    <i class="fas fa-camera me-1"></i> Change Profile Image
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="fas fa-envelope me-2 text-primary"></i> 
                                    <span class="fw-bold">Email:</span> 
                                    <?php echo htmlspecialchars($seller['email']); ?>
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-phone me-2 text-primary"></i> 
                                    <span class="fw-bold">Phone:</span> 
                                    <?php echo !empty($seller['phone']) ? htmlspecialchars($seller['phone']) : 'Not provided'; ?>
                                </li>
                                <li class="list-group-item">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i> 
                                    <span class="fw-bold">Address:</span> 
                                    <?php echo !empty($seller_profile['address']) ? htmlspecialchars($seller_profile['address']) : 'Not provided'; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Change Password Card -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="8" required>
                                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="8" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="update_password" class="btn btn-primary">
                                        <i class="fas fa-key me-1"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <!-- Edit Profile Card -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Edit Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($seller['username']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($seller['email']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($seller['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="business_name" class="form-label">Business Name</label>
                                        <input type="text" class="form-control" id="business_name" name="business_name" 
                                               value="<?php echo htmlspecialchars($seller_profile['business_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">Business Email</label>
                                        <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                               value="<?php echo htmlspecialchars($seller_profile['contact_email'] ?? ''); ?>">
                                        <small class="form-text text-muted">If different from personal email.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone" class="form-label">Business Phone</label>
                                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" 
                                               value="<?php echo htmlspecialchars($seller_profile['contact_phone'] ?? ''); ?>">
                                        <small class="form-text text-muted">If different from personal phone.</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($seller_profile['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">About Your Business</label>
                                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($seller_profile['description'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">Tell customers about your bookstore, specialties, etc.</small>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account Information Card -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Account Status:</strong> 
                                        <span class="badge bg-success">Active</span>
                                    </p>
                                    <p><strong>Account Type:</strong> Seller</p>
                                    <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($seller['created_at'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Last Login:</strong> <?php echo date('F d, Y h:i A', strtotime($seller['last_login'] ?? $seller['created_at'])); ?></p>
                                    <p><strong>Total Books:</strong> <?php echo $total_books; ?></p>
                                    <p><strong>Profile Completion:</strong> 
                                        <?php
                                        // Calculate profile completion percentage
                                        $completed_fields = 0;
                                        $total_fields = 7;
                                        
                                        if (!empty($seller['username'])) $completed_fields++;
                                        if (!empty($seller['email'])) $completed_fields++;
                                        if (!empty($seller['phone'])) $completed_fields++;
                                        if (!empty($seller_profile['business_name'])) $completed_fields++;
                                        if (!empty($seller_profile['contact_phone'])) $completed_fields++;
                                        if (!empty($seller_profile['address'])) $completed_fields++;
                                        if (!empty($seller_profile['description'])) $completed_fields++;
                                        
                                        $completion_percent = round(($completed_fields / $total_fields) * 100);
                                        ?>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: <?php echo $completion_percent; ?>%" 
                                                 aria-valuenow="<?php echo $completion_percent; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $completion_percent; ?>%
                                            </div>
                                        </div>
                                    </p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="fw-bold">Security Settings</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="enable2FA" disabled>
                                        <label class="form-check-label" for="enable2FA">Enable Two-Factor Authentication</label>
                                        <small class="d-block text-muted">Coming soon! Enhance your account security.</small>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="emailNotifications" checked>
                                        <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Profile Image Modal -->
<div class="modal fade" id="profileImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Profile Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="upload-profile-image.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Choose Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
                        <small class="form-text text-muted">Max file size: 2MB. Supported formats: JPG, JPEG, PNG.</small>
                    </div>
                    <div class="mb-3">
                        <div id="image_preview" class="text-center">
                            <img src="../assets/images/default-profile.jpg" alt="Preview" class="img-fluid rounded-circle" style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Preview profile image before upload
    document.getElementById('profile_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.querySelector('#image_preview img').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include_once 'includes/seller-footer.php'; ?>