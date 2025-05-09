<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (!is_valid_username($username)) {
        header('Location: register.php?error=Invalid+username');
        exit;
    }
    if (!is_valid_email($email)) {
        header('Location: register.php?error=Invalid+email');
        exit;
    }
    if (!is_valid_password($password)) {
        header('Location: register.php?error=Password+must+be+at+least+6+characters');
        exit;
    }

    $mysqli = get_db_connection();

    // Check for existing username or email
    $stmt = $mysqli->prepare('SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $mysqli->close();
        header('Location: register.php?error=Username+or+email+already+exists');
        exit;
    }
    $stmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $mysqli->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $email, $password_hash);
    if ($stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        header('Location: register.php?success=Registration+successful.+You+may+now+log+in.');
        exit;
    } else {
        $stmt->close();
        $mysqli->close();
        header('Location: register.php?error=Registration+failed.+Please+try+again.');
        exit;
    }
} else {
    header('Location: register.php');
    exit;
} 