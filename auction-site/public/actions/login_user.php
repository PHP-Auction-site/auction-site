<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/includes/db_connect.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = "Invalid request method.";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . SITE_URL . "/public/login.php");
    exit();
}

// Get and validate input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['flash_message'] = "Please fill in all fields.";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . SITE_URL . "/public/login.php");
    exit();
}

try {
    // Get database connection
    $mysqli = get_db_connection();
    
    // Prepare statement to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT user_id, username, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            // Set success message
            $_SESSION['flash_message'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";
            $_SESSION['flash_type'] = "success";
            
            // Redirect to dashboard
            header("Location: " . SITE_URL . "/public/dashboard.php");
            exit();
        }
    }
    
    // If we get here, either username doesn't exist or password is wrong
    $_SESSION['flash_message'] = "Invalid username or password.";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . SITE_URL . "/public/login.php");
    exit();
    
} catch (Exception $e) {
    // Log error (in production, use proper error logging)
    error_log("Login error: " . $e->getMessage());
    
    $_SESSION['flash_message'] = "An error occurred during login. Please try again.";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . SITE_URL . "/public/login.php");
    exit();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
} 