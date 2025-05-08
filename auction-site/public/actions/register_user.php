<?php
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/includes/db_connect.php';
require_once __DIR__ . '/../../src/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/public/register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (!is_valid_username($username)) {
    $errors[] = "Username must be 3-30 characters and can only contain letters, numbers, and underscores";
}

if (!is_valid_email($email)) {
    $errors[] = "Invalid email format";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

if (!empty($errors)) {
    set_flash_message(implode(', ', $errors), 'danger');
    header('Location: ' . SITE_URL . '/public/register.php');
    exit;
}

// Check if username or email already exists
$mysqli = get_db_connection();
$stmt = $mysqli->prepare('SELECT user_id FROM users WHERE username = ? OR email = ?');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $mysqli->close();
    set_flash_message('Username or email already exists', 'danger');
    header('Location: ' . SITE_URL . '/public/register.php');
    exit;
}

// Create new user
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $username, $email, $password_hash);

if ($stmt->execute()) {
    // Start session and log in
    session_start();
    $_SESSION['user_id'] = $mysqli->insert_id;
    $_SESSION['username'] = $username;
    
    set_flash_message('Registration successful! Welcome to Auction Site.', 'success');
    
    $mysqli->close();
    header('Location: ' . SITE_URL . '/public/index.php');
    exit;
} else {
    $mysqli->close();
    set_flash_message('Registration failed. Please try again.', 'danger');
    header('Location: ' . SITE_URL . '/public/register.php');
    exit;
} 