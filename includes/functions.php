<?php
// Common utility functions for validation, sanitization, etc.

require_once __DIR__ . '/../config.php';

function sanitize_output($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_username($username) {
    return preg_match('/^[A-Za-z0-9_]{3,30}$/', $username);
}

function is_valid_password($password) {
    return strlen($password) >= 6;
}

function format_price($price) {
    return '$' . number_format($price, 2);
}

function mark_ended_auctions() {
    $mysqli = get_db_connection();
    $now = (new DateTime('now', new DateTimeZone(TIMEZONE)))->format('Y-m-d H:i:s');
    $stmt = $mysqli->prepare('UPDATE items SET status = "Ended" WHERE status = "Active" AND end_time <= ?');
    $stmt->bind_param('s', $now);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

// Function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to format currency
function format_currency($amount) {
    return number_format($amount, 2, '.', ',');
}

// Function to format date/time
function format_datetime($datetime, $format = 'Y-m-d H:i:s') {
    $dt = new DateTime($datetime);
    $dt->setTimezone(new DateTimeZone(TIMEZONE));
    return $dt->format($format);
}

// Function to generate random string
function generate_random_string($length = 10) {
    return bin2hex(random_bytes($length));
}

// Function to validate file upload
function validate_image_upload($file) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid file upload');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new Exception('No file uploaded');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('File size exceeds limit');
        default:
            throw new Exception('Unknown file upload error');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new Exception('File size exceeds limit');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid file type');
    }

    return true;
}

// Function to save uploaded file
function save_uploaded_file($file, $prefix = '') {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . generate_random_string() . '.' . $extension;
    $upload_path = BASE_PATH . UPLOAD_PATH;
    
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    $filepath = $upload_path . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    return $filename;
}

/**
 * Set a flash message to be displayed on the next page load
 * @param string $message The message to display
 * @param string $type The type of message (success, danger, warning, info)
 */
function set_flash_message($message, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear the flash message
 * @return string HTML for the flash message, or empty string if no message
 */
function get_flash_message() {
    if (empty($_SESSION['flash_message'])) {
        return '';
    }

    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'info';

    // Clear the flash message
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);

    return "<div class='container'><div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                " . h($message) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div></div>";
}

// Placeholder for utility functions 