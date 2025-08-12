<?php
// views/admin/settings.php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php?redirect=admin/settings.php');
    exit();
}

// Include config files
require_once '../../config/config.php';
require_once '../../config/database.php';

// Include required models
require_once '../../models/Setting.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Initialize models
$settingModel = new Setting($db);

// Get all site settings
$settings = $settingModel->getAllSettings();

// Organize settings by group
$groupedSettings = [];
foreach ($settings as $setting) {
    if (!isset($groupedSettings[$setting['group']])) {
        $groupedSettings[$setting['group']] = [];
    }
    $groupedSettings[$setting['group']][] = $setting;
}

// Handle form submission
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = $_POST['settings'] ?? [];
    $success = true;
    
    foreach ($updates as $key => $value) {
        // Handle file uploads for logo settings
        if ($key === 'site_logo' || $key === 'site_favicon') {
            if (!empty($_FILES[$key]['name'])) {
                $file_name = $_FILES[$key]['name'];
                $file_tmp = $_FILES[$key]['tmp_name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Validate file type
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'];
                if (in_array($file_ext, $allowed_exts)) {
                    $new_file_name = $key . '_' . time() . '.' . $file_ext;
                    $upload_path = '../assets/images/' . $new_file_name;
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $settingModel->updateSetting($key, $new_file_name);
                    } else {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }
        } else {
            $settingModel->updateSetting($key, $value);
        }
    }
    
    if ($success) {
        $message = [
            'type' => 'success',
            'text' => 'Settings have been updated successfully.'
        ];
        
        // Refresh settings
        $settings = $settingModel->getAllSettings();
        $groupedSettings = [];
        foreach ($settings as $setting) {
            if (!isset($groupedSettings[$setting['group']])) {
                $groupedSettings[$setting['group']] = [];
            }
            $groupedSettings[$setting['group']][] = $setting;
        }
    } else {
        $message = [
            'type' => 'danger',
            'text' => 'There was an error updating some settings. Please try again.'
        ];
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
                <h1 class="h2">System Settings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Settings Tabs -->
            <div class="card shadow mb-4">
                <div class="card-header bg-white p-0">
                    <ul class="nav nav-tabs nav-pills card-header-tabs" id="settingsTabs" role="tablist">
                        <?php 
                        $firstTab = true;
                        foreach (array_keys($groupedSettings) as $group): 
                            $groupId = strtolower(str_replace(' ', '_', $group));
                        ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $firstTab ? 'active' : ''; ?>" 
                                        id="<?php echo $groupId; ?>-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#<?php echo $groupId; ?>" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="<?php echo $groupId; ?>" 
                                        aria-selected="<?php echo $firstTab ? 'true' : 'false'; ?>">
                                    <?php echo $group; ?>
                                </button>
                            </li>
                            <?php $firstTab = false; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                        <div class="tab-content" id="settingsTabsContent">
                            <?php 
                            $firstTab = true;
                            foreach ($groupedSettings as $group => $groupSettings): 
                                $groupId = strtolower(str_replace(' ', '_', $group));
                            ?>
                                <div class="tab-pane fade <?php echo $firstTab ? 'show active' : ''; ?>" 
                                     id="<?php echo $groupId; ?>" 
                                     role="tabpanel" 
                                     aria-labelledby="<?php echo $groupId; ?>-tab">
                                    
                                    <h5 class="mb-4"><?php echo $group; ?> Settings</h5>
                                    
                                    <div class="row g-4">
                                        <?php foreach ($groupSettings as $setting): ?>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="<?php echo $setting['key']; ?>" class="form-label">
                                                        <?php echo $setting['name']; ?>
                                                        <?php if (!empty($setting['description'])): ?>
                                                            <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($setting['description']); ?>"></i>
                                                        <?php endif; ?>
                                                    </label>
                                                    
                                                    <?php if ($setting['type'] === 'text'): ?>
                                                        <input type="text" class="form-control" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo htmlspecialchars($setting['value']); ?>">
                                                        
                                                    <?php elseif ($setting['type'] === 'textarea'): ?>
                                                        <textarea class="form-control" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" rows="3"><?php echo htmlspecialchars($setting['value']); ?></textarea>
                                                        
                                                    <?php elseif ($setting['type'] === 'number'): ?>
                                                        <input type="number" class="form-control" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo htmlspecialchars($setting['value']); ?>" step="<?php echo $setting['key'] === 'tax_rate' ? '0.01' : '1'; ?>">
                                                        
                                                    <?php elseif ($setting['type'] === 'email'): ?>
                                                        <input type="email" class="form-control" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo htmlspecialchars($setting['value']); ?>">
                                                        
                                                    <?php elseif ($setting['type'] === 'url'): ?>
                                                        <input type="url" class="form-control" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo htmlspecialchars($setting['value']); ?>">
                                                        
                                                    <?php elseif ($setting['type'] === 'select'): ?>
                                                        <select class="form-select" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]">
                                                            <?php
                                                            $options = json_decode($setting['options'], true);
                                                            foreach ($options as $optionKey => $optionValue):
                                                            ?>
                                                                <option value="<?php echo $optionKey; ?>" <?php echo $setting['value'] == $optionKey ? 'selected' : ''; ?>>
                                                                    <?php echo $optionValue; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        
                                                    <?php elseif ($setting['type'] === 'boolean'): ?>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="1" <?php echo $setting['value'] == 1 ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="<?php echo $setting['key']; ?>">
                                                                <?php echo $setting['value'] == 1 ? 'Enabled' : 'Disabled'; ?>
                                                            </label>
                                                        </div>
                                                        
                                                    <?php elseif ($setting['type'] === 'image'): ?>
                                                        <div class="mb-2">
                                                            <?php if (!empty($setting['value'])): ?>
                                                                <img src="../assets/images/<?php echo $setting['value']; ?>" alt="<?php echo $setting['name']; ?>" class="img-thumbnail mb-2" style="max-height: 100px;">
                                                            <?php endif; ?>
                                                        </div>
                                                        <input type="file" class="form-control" id="<?php echo $setting['key']; ?>" name="<?php echo $setting['key']; ?>" accept="image/*">
                                                        <input type="hidden" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo htmlspecialchars($setting['value']); ?>">
                                                        
                                                    <?php elseif ($setting['type'] === 'color'): ?>
                                                        <input type="color" class="form-control form-control-color" id="<?php echo $setting['key']; ?>" name="settings[<?php echo $setting['key']; ?>]" value="<?php echo htmlspecialchars($setting['value']); ?>" title="Choose a color">
                                                        
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($setting['help_text'])): ?>
                                                        <div class="form-text"><?php echo $setting['help_text']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php $firstTab = false; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Cache Management Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cache Management</h6>
                </div>
                <div class="card-body">
                    <p>
                        Clear various system caches to ensure that your changes take effect immediately.
                        This may be necessary after making significant changes to site settings.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-bolt text-warning me-2"></i>System Cache</h5>
                                    <p class="card-text">Clear all system cached data including settings and configurations.</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="clearCache('system')">
                                        Clear System Cache
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-image text-info me-2"></i>Image Cache</h5>
                                    <p class="card-text">Clear cached images and thumbnails to regenerate them.</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="clearCache('image')">
                                        Clear Image Cache
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-database text-danger me-2"></i>All Cache</h5>
                                    <p class="card-text">Clear all cached data. This may temporarily slow down your site.</p>
                                    <button type="button" class="btn btn-outline-danger" onclick="clearCache('all')">
                                        Clear All Cache
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>PHP Version:</th>
                                        <td><?php echo phpversion(); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Server Software:</th>
                                        <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Database Type:</th>
                                        <td>MySQL</td>
                                    </tr>
                                    <tr>
                                        <th>System Version:</th>
                                        <td>BookHaven v1.0.0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Total Books:</th>
                                        <td><?php echo (new Book($db))->getTotalBooks(); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Orders:</th>
                                        <td><?php echo (new Order($db))->getTotalOrders(); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Customers:</th>
                                        <td><?php echo (new User($db))->getTotalUsersByRole('customer'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Sellers:</th>
                                        <td><?php echo (new User($db))->getTotalUsersByRole('seller'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Toggle switch label update
        const toggleSwitches = document.querySelectorAll('.form-check-input[type="checkbox"]');
        toggleSwitches.forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (this.checked) {
                    label.textContent = 'Enabled';
                } else {
                    label.textContent = 'Disabled';
                }
            });
        });
    });
    
    // Clear cache function
    function clearCache(type) {
        // In a real app, this would make an AJAX call to a PHP script
        const button = event.target;
        const originalText = button.textContent;
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Clearing...';
        
        // Simulate API call
        setTimeout(() => {
            button.innerHTML = '<i class="fas fa-check me-1"></i> Cleared!';
            button.classList.remove('btn-outline-primary', 'btn-outline-danger');
            button.classList.add('btn-success');
            
            // Show alert
            const alertHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    ${type.charAt(0).toUpperCase() + type.slice(1)} cache cleared successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.querySelector('main').insertAdjacentHTML('afterbegin', alertHTML);
            
            // Reset button after delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                if (type === 'all') {
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-danger');
                } else {
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-primary');
                }
            }, 2000);
        }, 1500);
    }
</script>

<?php 
// Include required models for system information that weren't needed before
require_once '../models/Book.php';
require_once '../models/Order.php';

include_once 'includes/admin-footer.php'; 
?>