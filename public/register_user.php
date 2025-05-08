<?php
require_once __DIR__ . '/../src/includes/db_connect.php';
require_once __DIR__ . '/../src/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (!is_valid_username($username)) {
    $errors[] = "Invalid username format";
}

if (!is_valid_email($email)) {
    $errors[] = "Invalid email format";
}

if (!is_valid_password($password)) {
    $errors[] = "Password must be at least 6 characters long";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

if (!empty($errors)) {
    header('Location: register.php?error=' . urlencode(implode(', ', $errors)));
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
    header('Location: register.php?error=' . urlencode('Username or email already exists'));
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
    $_SESSION['flash_message'] = 'Registration successful! Welcome to Auction Site.';
    $_SESSION['flash_type'] = 'success';
    
    $mysqli->close();
    header('Location: index.php');
    exit;
} else {
    $mysqli->close();
    header('Location: register.php?error=' . urlencode('Registration failed. Please try again.'));
    exit;
} 