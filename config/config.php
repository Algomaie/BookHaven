<?php
// Application configurations
define('APP_NAME', 'bookhaven');
define('BASE_URL', 'http://localhost/bookhaven/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Database configurations
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'book_selling_db');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>