<?php
require_once __DIR__ . '/../src/includes/db_connect.php';
require_once __DIR__ . '/../src/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: login.php?error=' . urlencode('Please enter both username and password'));
    exit;
}

// Get user from database
$mysqli = get_db_connection();
$stmt = $mysqli->prepare('SELECT user_id, username, password_hash FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $mysqli->close();
    header('Location: login.php?error=' . urlencode('Invalid username or password'));
    exit;
}

// Start session and log in
session_start();
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['flash_message'] = 'Welcome back, ' . htmlspecialchars($user['username']) . '!';
$_SESSION['flash_type'] = 'success';

$mysqli->close();
header('Location: index.php');
exit; 