<?php
/**
 * Utility functions for the BookHaven application
 * This file contains helper functions used throughout the site
 */

// ============================
// Security Functions
// ============================

/**
 * Sanitize input data to prevent XSS attacks
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Verify CSRF token to prevent CSRF attacks
 * 
 * @param string $token Token from the form
 * @return bool True if token is valid, false otherwise
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !$token || $_SESSION['csrf_token'] !== $token) {
        return false;
    }
    return true;
}

/**
 * Generate a CSRF token and store it in the session
 * 
 * @return string Generated CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Check if the user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 * 
 * @return bool True if user is admin, false otherwise
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if the current user is a seller
 * 
 * @return bool True if user is seller, false otherwise
 */
function is_seller() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'seller';
}

/**
 * Redirect user to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash_message('error', 'Please log in to access this page.');
        redirect('views/auth/login.php');
        exit;
    }
}

/**
 * Redirect user if not admin
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash_message('error', 'Access denied. Admin privileges required.');
        redirect('index.php');
        exit;
    }
}

/**
 * Redirect user if not seller
 */
function require_seller() {
    require_login();
    if (!is_seller()) {
        set_flash_message('error', 'Access denied. Seller privileges required.');
        redirect('index.php');
        exit;
    }
}

// ============================
// Formatting Functions
// ============================

/**
 * Format currency amount based on site settings
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code (defaults to site setting)
 * @return string Formatted currency
 */
function format_currency($amount, $currency = null) {
    if ($currency === null) {
        // Get currency from site settings
        global $settingsArray;
        $currency = isset($settingsArray['currency']) ? $settingsArray['currency'] : 'USD';
    }
    
    $currencies = [
        'USD' => ['symbol' => '$', 'position' => 'before'],
        'EUR' => ['symbol' => '€', 'position' => 'after'],
        'GBP' => ['symbol' => '£', 'position' => 'before'],
        'CAD' => ['symbol' => 'C$', 'position' => 'before'],
        'AUD' => ['symbol' => 'A$', 'position' => 'before']
    ];
    
    $currencyInfo = isset($currencies[$currency]) ? $currencies[$currency] : $currencies['USD'];
    $formattedAmount = number_format($amount, 2, '.', ',');
    
    if ($currencyInfo['position'] === 'before') {
        return $currencyInfo['symbol'] . $formattedAmount;
    } else {
        return $formattedAmount . $currencyInfo['symbol'];
    }
}

/**
 * Format date in the specified format
 * 
 * @param string $date Date string
 * @param string $format Format string (default: 'M d, Y')
 * @return string Formatted date
 */
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Format date and time in the specified format
 * 
 * @param string $datetime Date and time string
 * @param string $format Format string (default: 'M d, Y h:i A')
 * @return string Formatted date and time
 */
function format_datetime($datetime, $format = 'M d, Y h:i A') {
    return date($format, strtotime($datetime));
}

/**
 * Get time elapsed string (e.g., "2 days ago")
 * 
 * @param string $datetime Date and time string
 * @return string Time elapsed
 */
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    if (!$string) {
        return 'just now';
    }
    
    $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Truncate text to a specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append Text to append (default: '...')
 * @return string Truncated text
 */
function truncate_text($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text . $append;
}

/**
 * Generate star rating HTML
 * 
 * @param float $rating Rating value (0-5)
 * @param int $maxStars Maximum number of stars (default: 5)
 * @return string HTML for star rating
 */
function star_rating_html($rating, $maxStars = 5) {
    $html = '<div class="rating">';
    $fullStars = floor($rating);
    $halfStar = $rating - $fullStars >= 0.5;
    $emptyStars = $maxStars - $fullStars - ($halfStar ? 1 : 0);
    
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    
    // Half star
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    
    $html .= ' <span class="rating-value">(' . number_format($rating, 1) . ')</span></div>';
    return $html;
}

// ============================
// URL and Navigation Functions
// ============================

/**
 * Redirect to a specified URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Get the current page name from the URL
 * 
 * @return string Current page name
 */
function get_current_page() {
    return basename($_SERVER['PHP_SELF'], '.php');
}

/**
 * Check if the current page matches a specified page
 * 
 * @param string|array $page Page name(s) to check
 * @return bool True if current page matches, false otherwise
 */
function is_current_page($page) {
    $currentPage = get_current_page();
    
    if (is_array($page)) {
        return in_array($currentPage, $page);
    }
    
    return $currentPage === $page;
}

/**
 * Generate a pagination HTML
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $url Base URL for pagination links
 * @param array $params Additional query parameters
 * @return string HTML for pagination
 */
function pagination($currentPage, $totalPages, $url, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $queryString = '';
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            if ($key !== 'page') {
                $queryString .= "&{$key}=" . urlencode($value);
            }
        }
    }
    
    $html = '<nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item">
            <a class="page-link" href="' . $url . '?page=' . ($currentPage - 1) . $queryString . '">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>';
    } else {
        $html .= '<li class="page-item disabled">
            <span class="page-link"><i class="fas fa-chevron-left"></i></span>
        </li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $startPage + 4);
    
    if ($endPage - $startPage < 4 && $startPage > 1) {
        $startPage = max(1, $endPage - 4);
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i === $currentPage) ? 'active' : '';
        $html .= '<li class="page-item ' . $activeClass . '">
            <a class="page-link" href="' . $url . '?page=' . $i . $queryString . '">' . $i . '</a>
        </li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item">
            <a class="page-link" href="' . $url . '?page=' . ($currentPage + 1) . $queryString . '">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>';
    } else {
        $html .= '<li class="page-item disabled">
            <span class="page-link"><i class="fas fa-chevron-right"></i></span>
        </li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

// ============================
// Flash Message Functions
// ============================

/**
 * Set a flash message that will be displayed on the next page load
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 */
function set_flash_message($type, $message) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display and clear all flash messages
 * 
 * @return string HTML for flash messages
 */
function display_flash_messages() {
    if (!isset($_SESSION['flash_messages']) || empty($_SESSION['flash_messages'])) {
        return '';
    }
    
    $html = '';
    
    foreach ($_SESSION['flash_messages'] as $message) {
        $type = $message['type'];
        $alertClass = 'alert-info';
        $icon = 'info-circle';
        
        switch ($type) {
            case 'success':
                $alertClass = 'alert-success';
                $icon = 'check-circle';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                $icon = 'exclamation-circle';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                $icon = 'exclamation-triangle';
                break;
        }
        
        $html .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
            <i class="fas fa-' . $icon . ' me-2"></i>' . $message['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
    
    // Clear the flash messages
    $_SESSION['flash_messages'] = [];
    
    return $html;
}

// ============================
// File Upload Functions
// ============================

/**
 * Handle file upload
 * 
 * @param array $file $_FILES array element
 * @param string $destinationPath Upload directory
 * @param array $allowedTypes Allowed file types
 * @param int $maxSize Maximum file size in bytes
 * @return array Upload result with status and message
 */
function upload_file($file, $destinationPath, $allowedTypes = [], $maxSize = 2097152) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $errorMessage = isset($errorMessages[$file['error']]) ? 
                         $errorMessages[$file['error']] : 
                         'Unknown upload error';
        
        return [
            'status' => false,
            'message' => $errorMessage
        ];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return [
            'status' => false,
            'message' => 'File size exceeds the limit of ' . format_file_size($maxSize)
        ];
    }
    
    // Check file type
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileMimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);
    
    // Get file extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!empty($allowedTypes) && !in_array($fileExtension, $allowedTypes)) {
        return [
            'status' => false,
            'message' => 'Only ' . implode(', ', $allowedTypes) . ' files are allowed'
        ];
    }
    
    // Create destination directory if it doesn't exist
    if (!file_exists($destinationPath)) {
        mkdir($destinationPath, 0755, true);
    }
    
    // Generate a unique filename
    $newFilename = uniqid() . '.' . $fileExtension;
    $destinationFile = $destinationPath . '/' . $newFilename;
    
    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $destinationFile)) {
        return [
            'status' => true,
            'message' => 'File uploaded successfully',
            'filename' => $newFilename,
            'path' => $destinationFile
        ];
    } else {
        return [
            'status' => false,
            'message' => 'Failed to move uploaded file'
        ];
    }
}

/**
 * Format file size in human-readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// ============================
// Validation Functions
// ============================

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 * 
 * @param string $url URL to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @param int $minLength Minimum password length
 * @return array Validation result with status and message
 */
function validate_password($password, $minLength = 8) {
    $result = [
        'status' => true,
        'message' => 'Password is valid'
    ];
    
    if (strlen($password) < $minLength) {
        $result['status'] = false;
        $result['message'] = "Password must be at least {$minLength} characters long";
        return $result;
    }
    
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        $result['status'] = false;
        $result['message'] = 'Password must contain at least one lowercase letter';
        return $result;
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $result['status'] = false;
        $result['message'] = 'Password must contain at least one uppercase letter';
        return $result;
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        $result['status'] = false;
        $result['message'] = 'Password must contain at least one number';
        return $result;
    }
    
    // Check for at least one special character
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $result['status'] = false;
        $result['message'] = 'Password must contain at least one special character';
        return $result;
    }
    
    return $result;
}

/**
 * Validate phone number
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_phone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if the length is valid (10-15 digits)
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// ============================
// Cart Functions
// ============================

/**
 * Initialize the shopping cart in session if not already done
 */
function initialize_cart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add an item to the cart
 * 
 * @param int $bookId Book ID
 * @param string $title Book title
 * @param float $price Book price
 * @param int $quantity Quantity to add
 * @param string $coverImage Cover image path
 * @return array Result of the operation
 */
function add_to_cart($bookId, $title, $price, $quantity = 1, $coverImage = '') {
    initialize_cart();
    
    // Check if the item is already in the cart
    $itemIndex = -1;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['book_id'] == $bookId) {
            $itemIndex = $index;
            break;
        }
    }
    
    if ($itemIndex >= 0) {
        // Update quantity if item exists
        $_SESSION['cart'][$itemIndex]['quantity'] += $quantity;
        return [
            'status' => true,
            'message' => 'Item quantity updated in cart',
            'action' => 'updated'
        ];
    } else {
        // Add new item
        $_SESSION['cart'][] = [
            'book_id' => $bookId,
            'title' => $title,
            'price' => $price,
            'quantity' => $quantity,
            'cover_image' => $coverImage
        ];
        return [
            'status' => true,
            'message' => 'Item added to cart',
            'action' => 'added'
        ];
    }
}

/**
 * Update cart item quantity
 * 
 * @param int $bookId Book ID
 * @param int $quantity New quantity
 * @return array Result of the operation
 */
function update_cart_quantity($bookId, $quantity) {
    initialize_cart();
    
    if ($quantity <= 0) {
        return remove_from_cart($bookId);
    }
    
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['book_id'] == $bookId) {
            $_SESSION['cart'][$index]['quantity'] = $quantity;
            return [
                'status' => true,
                'message' => 'Cart updated',
                'action' => 'updated'
            ];
        }
    }
    
    return [
        'status' => false,
        'message' => 'Item not found in cart',
        'action' => 'error'
    ];
}

/**
 * Remove an item from the cart
 * 
 * @param int $bookId Book ID to remove
 * @return array Result of the operation
 */
function remove_from_cart($bookId) {
    initialize_cart();
    
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['book_id'] == $bookId) {
            unset($_SESSION['cart'][$index]);
            // Reindex the array
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            return [
                'status' => true,
                'message' => 'Item removed from cart',
                'action' => 'removed'
            ];
        }
    }
    
    return [
        'status' => false,
        'message' => 'Item not found in cart',
        'action' => 'error'
    ];
}

/**
 * Get the cart contents
 * 
 * @return array Cart items
 */
function get_cart_items() {
    initialize_cart();
    return $_SESSION['cart'];
}

/**
 * Get the cart total
 * 
 * @return float Cart total price
 */
function get_cart_total() {
    initialize_cart();
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

/**
 * Get the total number of items in the cart
 * 
 * @return int Number of items
 */
function get_cart_count() {
    initialize_cart();
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

/**
 * Clear the cart
 * 
 * @return array Result of the operation
 */
function clear_cart() {
    $_SESSION['cart'] = [];
    
    return [
        'status' => true,
        'message' => 'Cart cleared',
        'action' => 'cleared'
    ];
}

// ============================
// Log Functions
// ============================

/**
 * Log user activity
 * 
 * @param string $action Action performed
 * @param string $description Description of the action
 * @param int $userId User ID (null for system actions)
 */
function log_activity($action, $description, $userId = null) {
    global $db;
    
    if (!$db) {
        return false;
    }
    
    $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $userId, $action, $description, $ipAddress);
    
    return $stmt->execute();
}

/**
 * Log system error
 * 
 * @param string $message Error message
 * @param string $file File where the error occurred
 * @param int $line Line number
 */
function log_error($message, $file = null, $line = null) {
    $logDir = __DIR__ . '/../../logs';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/error_log_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] ";
    
    if ($file && $line) {
        $logMessage .= "{$file}:{$line} - ";
    }
    
    $logMessage .= $message . PHP_EOL;
    
    return file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// ============================
// Settings Functions
// ============================

/**
 * Get all settings as an associative array
 * 
 * @param object $db Database connection
 * @return array Settings
 */
function get_all_settings($db) {
    $settings = [];
    
    $sql = "SELECT * FROM settings";
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['key']] = $row['value'];
        }
    }
    
    return $settings;
}

/**
 * Get a specific setting value
 * 
 * @param string $key Setting key
 * @param object $db Database connection
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value
 */
function get_setting($key, $db, $default = null) {
    $sql = "SELECT value FROM settings WHERE `key` = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['value'];
    }
    
    return $default;
}

// ============================
// Miscellaneous Functions
// ============================

/**
 * Generate a random string
 * 
 * @param int $length String length
 * @param string $keyspace Characters to use
 * @return string Random string
 */
function random_string($length = 10, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    
    return $str;
}

/**
 * Send email (wrapper for mail function)
 * 
 * @param string $to Recipient
 * @param string $subject Email subject
 * @param string $message Email body
 * @param array $headers Additional headers
 * @return bool True if email sent, false otherwise
 */
function send_email($to, $subject, $message, $headers = []) {
    global $settingsArray;
    
    $defaultHeaders = [
        'From' => isset($settingsArray['from_name']) && isset($settingsArray['from_email']) ? 
                  $settingsArray['from_name'] . ' <' . $settingsArray['from_email'] . '>' : 
                  'BookHaven <noreply@bookhaven.com>',
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    $headers = array_merge($defaultHeaders, $headers);
    $headerString = '';
    
    foreach ($headers as $name => $value) {
        $headerString .= $name . ': ' . $value . "\r\n";
    }
    
    return mail($to, $subject, $message, $headerString);
}

/**
 * Generate breadcrumbs
 * 
 * @param array $items Breadcrumb items [url => label]
 * @return string HTML for breadcrumbs
 */
function breadcrumbs($items) {
    $html = '<nav aria-label="breadcrumb">
        <ol class="breadcrumb">';
    
    $lastItem = end($items);
    reset($items);
    
    foreach ($items as $url => $label) {
        if ($label === $lastItem) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . $label . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . $label . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    
    return $html;
}

/**
 * Auto-loader function for class files
 * 
 * @param string $className Class name
 */
function class_autoloader($className) {
    $classPath = __DIR__ . '/../../models/' . $className . '.php';
    
    if (file_exists($classPath)) {
        require_once $classPath;
    }
}

// Register the autoloader
spl_autoload_register('class_autoloader');
?>