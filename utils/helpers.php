 
<?php
// ملف المساعدات - دوال مفيدة للتطبيق
// Helper functions for the application

/**
 * تنظيف المدخلات
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if($conn) {
        $data = mysqli_real_escape_string($conn, $data);
    }
    return $data;
}

/**
 * إعادة توجيه إلى صفحة معينة
 * Redirect to a specific page
 * @param string $location
 */
function redirect($location) {
    header("Location: $location");
    exit;
}

/**
 * تعيين رسالة في الجلسة
 * Set message in session
 * @param string $type
 * @param string $msg
 */
function set_message($type, $msg) {
    $_SESSION['message'] = [
        'type' => $type,
        'content' => $msg
    ];
}

/**
 * عرض الرسالة من الجلسة
 * Display message from session
 * @return string
 */
function display_message() {
    if(isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $output = '';
        
        switch($message['type']) {
            case 'success':
                $output = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">';
                break;
            case 'error':
                $output = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">';
                break;
            default:
                $output = '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">';
        }
        
        $output .= $message['content'] . '</div>';
        
        // حذف الرسالة بعد عرضها
        // Delete message after displaying it
        unset($_SESSION['message']);
        
        return $output;
    }
    
    return '';
}

/**
 * تنسيق السعر مع رمز العملة
 * Format price with currency symbol
 * @param float $price
 * @return string
 */
function format_price($price) {
    return '$' . number_format($price, 2);
}

/**
 * إنشاء HTML لتقييم النجوم
 * Create HTML for star rating
 * @param float $rating
 * @return string
 */
function star_rating($rating) {
    $fullStars = floor($rating);
    $halfStar = $rating - $fullStars >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $html = '<div class="flex text-yellow-500">';
    
    // النجوم الكاملة
    // Full stars
    for($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }
    
    // نصف نجمة
    // Half star
    if($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }
    
    // النجوم الفارغة
    // Empty stars
    for($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * التحقق من تسجيل دخول المستخدم
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * التحقق من كون المستخدم مسؤول
 * Check if user is admin
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * التحقق من كون المستخدم بائع
 * Check if user is seller
 * @return bool
 */
function is_seller() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'seller';
}

/**
 * التحقق من كون المستخدم زبون
 * Check if user is customer
 * @return bool
 */
function is_customer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
}
?>