<?php
/**
 * Validation Functions
 */

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate text length
 * @param string $text Text to validate
 * @param int $min Minimum length
 * @param int $max Maximum length
 * @return bool
 */
function is_valid_length($text, $min, $max) {
    $length = strlen($text);
    return ($length >= $min && $length <= $max);
}

/**
 * Check if passwords match
 * @param string $password Password
 * @param string $confirm Confirmation password
 * @return bool
 */
function passwords_match($password, $confirm) {
    return $password === $confirm;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return bool
 */
function is_strong_password($password) {
    // At least 8 characters, one uppercase, one lowercase, one number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

/**
 * Validate phone number
 * @param string $phone Phone number to validate
 * @return bool
 */
function is_valid_phone($phone) {
    // Accept numbers, dashes, parentheses, and spaces
    return preg_match('/^[0-9()\-\s]+$/', $phone);
}
?>